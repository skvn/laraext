<?php namespace Laraext\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


trait LoggerTrait {

    protected $logger = null;


    protected function getLogger()
    {
        if (is_null($this->logger))
        {
            $this->createLogger();
        }
        return $this->logger;
    }

    protected function createLogger()
    {
        $this->logger = new Logger(property_exists($this, "__log_title") ? $this->__log_title : "");
        $file = property_exists($this, "__log_file") ? storage_path("logs/" . $this->__log_file) : storage_path("logs/common.log");
        if (strpos($file, '%') !== false)
        {
            $file = str_replace("%d", date("Ymd"), $file);
        }
        if (!file_exists(dirname($file)))
        {
            mkdir(dirname($file));
        }
        $this->logger->pushHandler(new StreamHandler($file), Logger::INFO);
    }


}