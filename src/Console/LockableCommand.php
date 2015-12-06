<?php namespace Laraext\Console;

use Illuminate\Console\Command as LaravelCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LockableCommand extends LaravelCommand
{
    protected $laraext_pid = 0;
    protected $laraext_uid = 0;
    protected $laraext_lock = null;
    protected $laraext_single = false;


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pid = posix_getpid();
        $this->uid = posix_getuid();
        $this->laraext_lock = storage_path('locks/laraext.' . $this->laraext_pid);
        $this->lock();
        $result = null;
        try
        {
            $result = parent :: execute($input, $output);
            $this->unlock();
        }
        catch (\Exception $e)
        {
            $this->unlock();
            throw $e;
        }
        return $result;
    }

    protected function lock()
    {
        if (!file_exists(storage_path('locks')))
        {
            mkdir(storage_path('locks'));
        }
        $info = [
            'args' => $this->argument(),
            'opts' => $this->option()
        ];
        file_put_contents($this->laraext_lock, json_encode($info) . "\n");
    }

    protected function unlock()
    {
        if (file_exists($this->laraext_lock))
        {
            unlink($this->laraext_lock);
        }
    }

}
