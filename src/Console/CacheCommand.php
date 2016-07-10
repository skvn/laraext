<?php namespace Laraext\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CacheCommand extends LockableCommand {

    protected $signature = 'laraext:cache {method} {--tag=}';
    protected $description = 'Execute operation on cache';



    public function handle()
    {
        $method = camel_case('cmd_' . $this->argument('method'));
        $this->$method();
    }

    protected function cmdDumpKeys()
    {
        $keys = \LaraextCache :: dumpMemcacheKeys();
        foreach ($keys as $key)
        {
            $this->info($key);
        }
    }

    protected function cmdFlushTag()
    {
        $tag = $this->option('tag');
        if (empty($tag))
        {
            throw new Exception("--tag option is not set");
        }
        \LaraextCache :: forgetTag($tag);
        $this->info($tag . " cached data flushed");
    }





}
