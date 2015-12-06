<?php namespace Laraext\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;


trait LoggerTrait {

    protected $loggers = [];


    protected function getLogger($name = null)
    {
        if (empty($name))
        {
            $name = "_default_";
        }
        if (!isset($this->loggers[$name]))
        {
            $this->createLogger($name);
        }
        return $this->loggers[$name];
    }

    protected function createLogger($name)
    {
        $logger_title = "";
        if ($name == "_default_")
        {
            $logger_title = property_exists($this, "__log_title") ? $this->__log_title : "";
        }
        if ($name == "_default_")
        {
            $file = property_exists($this, "__log_file") ? storage_path("logs/" . $this->__log_file) : storage_path("logs/common.log");
        }
        else
        {
            if (strpos($name, "!") === 0)
            {
                $file = storage_path(str_replace("!", "", $name));
            }
            else
            {
                $file = storage_path("logs/" . $name . ".log");
            }
        }
        if (strpos($file, '%') !== false)
        {
            $file = str_replace("%d", date("Ymd"), $file);
        }
        if (!file_exists(dirname($file)))
        {
            mkdir(dirname($file));
        }
        $this->loggers[$name] = new Logger($logger_title, [], [new WebProcessor()]);
        $this->loggers[$name]->pushHandler($handler = new StreamHandler($file), Logger::INFO);
        $handler->setFormatter(new Formatter\LineFormatter(null, null, true, true));
    }


}