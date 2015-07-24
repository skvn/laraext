<?php namespace Laraext\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LogRotateCommand extends Command {

    protected $name = 'laraext:logrotate';
    protected $description = 'Rotating logs';



    public function handle()
    {
        $removed = [];
        foreach (\Config :: get('laraext.logrotate') as $pattern => $rules)
        {
            $files =[];
            $dirs = [];
            $list = \File :: files(dirname(storage_path($pattern)));
            $dirlist = \File :: directories(dirname(storage_path($pattern)));
            foreach ($list as $f)
            {
                if (preg_match("#^".str_replace('%d', '\d+', basename($pattern))."$#", basename($f)))
                {
                    $files[] = $f;
                }
                if (preg_match("#^".str_replace('*', '.+', basename($pattern))."$#", basename($f)))
                {
                    $files[] = $f;
                }
            }
            foreach ($dirlist as $d)
            {
                if (preg_match("#^".str_replace('%d', '\d+', basename($pattern))."$#", basename($d)))
                {
                    $dirs[] = $d;
                }
                if (preg_match("#^".str_replace('*', '.+', basename($pattern))."$#", basename($d)))
                {
                    $dirs[] = $d;
                }
            }
            rsort($files);
            rsort($dirs);
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
                    if (!empty($rules['exclude_size_gt']))
                    {
                        if (filesize($file) > $rules['exclude_size_gt']*1024*1024)
                        {
                            continue;
                        }
                    }
                    unlink($file);
                    $removed[] = $file;
                }
            }
            if (!empty($rules['keep_dir']))
            {
                foreach ($dirs as $ind => $dir)
                {
                    if ($ind < $rules['keep_dir'])
                    {
                        continue;
                    }
                    \File :: deleteDirectory($dir);
                    $removed[] = $dir;
                }
            }

        }
        if ($mailto = \Config :: get('laraext.log.mailto'))
        {
            \Mail :: raw(implode("\n", $removed), function($message) use ($mailto){
                foreach (explode(",", $mailto) as $mail)
                {
                    $message->to($mail);
                }
                $subject = "Logs rotation on %i";
                $subject = str_replace('%u', \Config :: get('app.url'), $subject);
                $subject = str_replace('%i', \Config :: get('app.instance_name'), $subject);
                $message->subject($subject);
            });
        }
    }





}
