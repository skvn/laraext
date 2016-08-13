<?php namespace Skvn\Laraext\Console;

use Skvn\Laraext\Log\LoggerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


trait LockableCommandTrait
{
    use LoggerTrait;

    protected $laraext_pid = 0;
    protected $laraext_uid = 0;
    protected $laraext_lock = null;
    protected $laraext_single = false;
    protected $laraext_state = null;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $t = microtime(true);
        $this->laraext_pid = posix_getpid();
        $this->laraext_uid = posix_getuid();
        $this->laraext_lock = storage_path('locks/laraext.' . $this->laraext_pid);
        if ($this->isExclusive())
        {
            if ($this->isLocked())
            {
                $this->log('LOCKED[' . round(microtime(true) - $t, 2) .']: ' . $this->name . " " . json_encode($this->argument()), '!locks/laraext.log');
                throw new \Exception("Command already executing");
            }
        }
        $this->lock();
        $result = null;
        try
        {
            $result = parent :: execute($input, $output);
            $this->unlock();
            $this->log('SUCCESS[' . round(microtime(true) - $t, 2) .']: ' . $this->name . " " . json_encode($this->argument()), '!locks/laraext.log');
        }
        catch (\Exception $e)
        {
            $this->unlock();
            $this->log('ERROR[' . round(microtime(true) - $t, 2) . ']: ' . $this->name . " " . json_encode($this->argument()), '!locks/laraext.log');
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
            'name' => $this->getCommandName(),
            'args' => $this->argument(),
            'opts' => $this->option(),
            'started' => date('Y-m-d H:i:s'),
            'exclusive' => $this->isExclusive(),
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
        if (is_null($this->laraext_state))
        {
            $list = $this->laravel['files']->files(storage_path('locks'));
            $this->lataext_state = [];
            foreach ($list as $file)
            {
                if (preg_match("#.+laraext\.(\d+)$#", $file, $matches))
                {
                    $job = json_decode(trim(file($file)[0]), true);
                    $job['pid'] = $matches[1];
                    $job['state'] = @pcntl_getpriority($job['pid']) === false ? "KILLED" : "ALIVE";
                    $job['lock'] = $file;
                    $this->laraext_state[] = $job;
                }
            }
        }
        return $this->laraext_state;
    }

    protected function isLocked()
    {
        $job = $this->getLock();
        if ($job !== false)
        {
            return true;
        }
        return false;
    }

    protected function getLock($name = null, $args = null)
    {
        if (is_null($name))
        {
            $name = $this->getCommandName();
        }
        if (is_null($args))
        {
            $args = $this->argument();
        }
        $jobs = $this->getLocks();
        foreach ($jobs as $job)
        {
            if ($job['name'] == $name && $job['pid'] != $this->laraext_pid && $job['state'] == "ALIVE")
            {
                if (count($args) == count($job['args']))
                {
                    foreach ($job['args'] as $k => $v)
                    {
                        if (!isset($args[$k]) || $args[$k] != $v)
                        {
                            break;
                        }
                    }
                    return $job;
                }
            }
        }
        return false;
    }

    protected function isExclusive()
    {
        return false;
    }

    protected function getCommandName()
    {
        if (!empty($this->name))
        {
            return $this->name;
        }
        return explode(' ', $this->signature)[0];
    }

}