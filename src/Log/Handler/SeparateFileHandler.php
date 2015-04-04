<?php namespace Laraext\Log\Handler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SeparateFileHandler extends StreamHandler
{
    protected function write(array $record)
    {
        $target = \Config :: get('laraext.log.main');
        $exception_logs = \Config :: get('laraext.log.exceptions');
        $class = class_basename($record['context']['exception']);
        $callbacks = \Config :: get('laraext.errors.exception_callbacks');
        if (array_key_exists($class, $exception_logs))
        {
            $target = $exception_logs[$class];
        }
        if (array_key_exists($class, $callbacks))
        {
            list($cls, $method) = explode('@', $callbacks[$class]);
            $obj = new $cls;
            $obj->$method($record['context']['exception']);
        }
        if (strpos($target, '%') !== false)
        {
            $target = str_replace("%d", date("Ymd"), $target);
        }

        error_log((string) $record['formatted'], 3, storage_path($target));

        if ($mailto = \Config :: get('laraext.log.mailto'))
        {
            if ($only = \Config :: get('laraext.log.mailto_only'))
            {
                if (!in_array($class, $only))
                {
                    return;
                }
            }
            if ($except = \Config :: get('laraext.log.mailto_except'))
            {
                if (in_array($class, $except))
                {
                    return;
                }
            }
            \Mail :: raw((string) $record['formatted'], function($message) use ($mailto){
                foreach (explode(",", $mailto) as $mail)
                {
                    $message->to($mail);
                }
                $subject = \Config :: get('laraext.log.mailto_subject');
                $subject = str_replace('%u', \Config :: get('app.url'), $subject);
                $subject = str_replace('%i', \Config :: get('app.instance_name'), $subject);
                $message->subject($subject);
            });
        }
    }

}
