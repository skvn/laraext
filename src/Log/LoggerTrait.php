<?php namespace Skvn\Laraext\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;


trait LoggerTrait {

    protected $loggers = [];
    private $__laraext_console = null;


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

    function log($message, $file = "laraext")
    {
        $this->getLogger($file)->info($message);
    }

    function logConsole($message)
    {
        $con = $this->getLaraextConsole();
        if ($message !== '__laraext__' && $con)
        {
            $con->debug($message);
        }
        return;

    }

    private function getLaraextConsole()
    {
        if (is_null($this->__laraext_console))
        {
            $params = \Config :: get("laraext.console");
            if (!$params['enabled'])
            {
                return null;
            }
            if (empty($GLOBALS['laraext_console_storage_installed']))
            {
                \PhpConsole\Connector::setPostponeStorage(new \PhpConsole\Storage\File(storage_path('laraext-php-console.dat'), true));
                $GLOBALS['laraext_console_storage_installed'] = true;
            }
            $connector = \PhpConsole\Connector :: getInstance();
            if (!empty($params['password']))
            {
                $connector->setPassword($params['password'], true);
            }
            if (!empty($params['ips']))
            {
                $connector->setAllowedIpMasks(explode(",", $params['ips']));
            }
            $this->__laraext_console = \PhpConsole\Handler :: getInstance();
            if (!$this->__laraext_console->isStarted())
            {
                if (empty($params['catch_errors']))
                {
                    $this->__laraext_console->setHandleErrors(false);
                    $this->__laraext_console->setHandleExceptions(false);
                    $this->__laraext_console->setCallOldHandlers(false);
                }
                $this->__laraext_console->start();
            }
        }
        return $this->__laraext_console;
    }



}