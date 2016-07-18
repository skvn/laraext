<?php namespace Skvn\Laraext\Bootstrap;

use Illuminate\Foundation\Bootstrap\HandleExceptions as LaravelHandleExceptions;
use ErrorException;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

class HandleExceptions extends LaravelHandleExceptions{


    public function bootstrap(Application $app)
    {
        $this->app = $app;

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        ini_set('display_errors', $this->app['config']['laraext.errors.display']);

    }

    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if (error_reporting() & $level)
        {
            if ($level & $this->app['config']['laraext.errors.skip_halt_on'])
            {
                $this->getExceptionHandler()->report(new ErrorException($message, 0, $level, $file, $line));
            }
            else
            {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        }
    }

    public function handleException($e)
    {
        $this->getExceptionHandler()->report($e);

        if ($this->app->runningInConsole())
        {
            $this->renderForConsole($e);
        }
        else
        {
            $this->renderHttpResponse($e);
        }
    }

    public function handleShutdown()
    {
        if ( ! is_null($error = error_get_last()) && $this->isFatal($error['type']))
        {
            $this->handleException($this->fatalExceptionFromError($error));
        }
    }


}
