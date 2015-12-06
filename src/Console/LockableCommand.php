<?php namespace Laraext\Console;

use Illuminate\Console\Command as LaravelCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Laraext\Log\LoggerTrait;

class LockableCommand extends LaravelCommand
{
    use LoggerTrait;

    protected $laraext_pid = 0;
    protected $laraext_uid = 0;
    protected $laraext_lock = null;
    protected $laraext_single = false;


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pid = posix_getpid();
        $this->uid = posix_getuid();
        $this->laraext_lock = storage_path('locks/laraext.' . $this->laraext_pid);
        $t = microtime(true);
        $this->lock();
        $result = null;
        try
        {
            $result = parent :: execute($input, $output);
            $this->unlock();
            $this->getLogger('!locks/laraext.log')->info('SUCCESS[' . round(microtime(true) - $t, 2) .']: ' . $this->name . " " . json_encode($this->argument()));
        }
        catch (\Exception $e)
        {
            $this->unlock();
            $this->getLogger('!locks/laraext.log')->error('ERROR[' . round(microtime(true) - $t, 2) . ']: ' . $this->name . " " . json_encode($this->argument()));
            throw $e;
        }
        return $result;
    }

    protected function lock()
    {
        if (!$this->laravel['files']->exists(storage_path('locks')))
        {
            $this->laravel['files']->makeDirectory(storage_path('locks'));
        }
        $info = [
            'name' => $this->name,
            'args' => $this->argument(),
            'opts' => $this->option(),
            'started' => date('Y-m-d H:i:s')
        ];
        $this->laravel['files']->put($this->laraext_lock, json_encode($info) . "\n");
    }

    protected function unlock()
    {
        if ($this->laravel['files']->exists($this->laraext_lock))
        {
            $this->laravel['files']->delete($this->laraext_lock);
        }
    }


}
