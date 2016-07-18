<?php namespace Skvn\Laraext\Bootstrap;

use Illuminate\Foundation\Bootstrap\ConfigureLogging as LaravelConfigureLogging;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;
use Monolog\Processor\WebProcessor;
use Skvn\Laraext\Log\Formatter\LineFormatter;
use Illuminate\Contracts\Foundation\Application;
use Skvn\Laraext\Log\Handler\SeparateFileHandler;

class ConfigureLogging extends LaravelConfigureLogging{

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->configureHandlers($app, $this->registerLogger($app));

        // Next, we will bind a Closure that resolves the PSR logger implementation
        // as this will grant us the ability to be interoperable with many other
        // libraries which are able to utilize the PSR standardized interface.
        $app->bind('Psr\Log\LoggerInterface', function($app)
        {
            return $app['log']->getMonolog();
        });
    }

    /**
     * Register the logger instance in the container.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return \Illuminate\Log\Writer
     */
    protected function registerLogger(Application $app)
    {
        $app->instance('log', $log = new Writer(
            new Monolog($app->environment(), [], [new WebProcessor()]), $app['events'])
        );

        return $log;
    }


    protected function configureSeparateHandler(Application $app, Writer $log)
    {
        $log->getMonolog()->pushHandler(
            $handler = new SeparateFileHandler("dummy")
        );
        $handler->setFormatter(new LineFormatter(null, null, true, true));
    }

}
