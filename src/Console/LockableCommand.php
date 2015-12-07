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
    protected $laraext_exclusive = false;


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->laraext_pid = posix_getpid();
        $this->laraext_uid = posix_getuid();
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
            'started' => date('Y-m-d H:i:s'),
            'exclusive' => $this->laraext_exclusive
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

    protected function getLocks()
    {
        $list = $this->laravel['files']->files(storage_path('locks'));
        $jobs = [];
        foreach ($list as $file)
        {
            if (preg_match("#.+laraext\.(\d+)$#", $file, $matches))
            {
                $job = json_decode(trim(file($file)[0]), true);
                $job['pid'] = $matches[1];
                $job['state'] = @pcntl_getpriority($job['pid']) === false ? "KILLED" : "ALIVE";
                $jobs[] = $job;
            }
        }
        return $jobs;
    }

    protected function isLocked()
    {
        $jobs = $this->getLocks();
        foreach ($jobs as $job)
        {
            if ($job['name'] == $this->name && $job['pid'] != $this->laraext_pid)
            {
                $args = $this->argument();
                if (count($args) == count($job['args']))
                {
                    foreach ($job['args'] as $k => $v)
                    {
                        if (!isset($args[$k]) || $args[$k] != $v)
                        {
                            break;
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }


}
