<?php namespace Skvn\Laraext\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class JobsCommand extends LockableCommand {

    protected $name = 'laraext:jobs';
    protected $description = 'artisan jobs utilities';



    public function handle()
    {
        $jobs = $this->getLocks();
        foreach ($jobs as $job)
        {
            if ($job['state'] == "KILLED")
            {
                $this->error($job['pid'] . ". " . $job['name']);
            }
            else
            {
                $this->line($job['pid'] . ". " . $job['name']);
            }
        }
    }





}
