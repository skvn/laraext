<?php namespace Skvn\Laraext\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class JobsCommand extends LockableCommand {

    protected $signature = 'laraext:jobs {--clean}';
    protected $description = 'artisan jobs utilities';



    public function handle()
    {
        $jobs = $this->getLocks();
        foreach ($jobs as $job)
        {
            if ($job['state'] == "KILLED")
            {
                $this->error($job['pid'] . ". " . $job['name']);
                if ($this->option("clean"))
                {
                    $this->laravel['files']->delete($job['lock']);
                    $this->info('REMOVED');
                }
            }
            else
            {
                $this->line($job['pid'] . ". " . $job['name']);
            }
        }
    }





}
