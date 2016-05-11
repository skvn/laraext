<?php namespace Laraext\Log\Handler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Laraext\Log\LoggerTrait;

class SeparateFileHandler extends StreamHandler
{
    use LoggerTrait;

    protected $last_exception = null;

    protected function write(array $record)
    {
        $target = \Config :: get('laraext.log.main');
        $exception_logs = \Config :: get('laraext.log.exceptions');
        $class = null;
        if (isset($record['context']['exception']))
        {
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
        }
        if (strpos($target, '%') !== false)
        {
            $target = str_replace("%d", date("Ymd"), $target);
        }

        if (!empty($record['context']['browsify']))
        {
            $this->logConsole($record['message']);
        }


        error_log((string) $record['formatted'], 3, storage_path($target));

        if ($mailto = \Config :: get('laraext.log.mailto'))
        {
            if ($only = \Config :: get('laraext.log.mailto_only'))
            {
                if (is_null($class) || !in_array($class, $only))
                {
                    return;
                }
            }
            if ($except = \Config :: get('laraext.log.mailto_except'))
            {
                if (is_null($class) || in_array($class, $except))
                {
                    return;
                }
            }
            if ($this->last_exception != (string) $record['message'])
            {
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
            $this->last_exception = (string) $record['message'];
        }
    }


}
