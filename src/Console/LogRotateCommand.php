<?php namespace Laraext\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LogRotateCommand extends Command {

    protected $name = 'laraext:logrotate';
    protected $description = 'Rotating logs';



    public function handle()
    {
        foreach (\Config :: get('laraext.logrotate') as $pattern => $rules)
        {
            $files = \File :: glob(storage_path(str_replace('%d', '*', $pattern)));
            sort($files);
            if (!empty($rules['keep']))
            {
                foreach ($files as $ind => $file)
                {
                    if ($ind < $rules['keep'])
                    {
                        continue;
                    }
                    if (!empty($rules['exclude']))
                    {
                        if (preg_match('#' . $rules['exclude'] . '#', $file))
                        {
                            continue;
                        }
                    }
                }
            }
        }
    }





}
