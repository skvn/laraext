<?php namespace Laraext;

use Illuminate\Support\ServiceProvider as LServiceProvider;
use Laraext\Cache\MemcachedStore;

class ServiceProvider extends LServiceProvider {


    public function boot()
    {
        $this->registerCache();
    }

    public function register()
    {
        $this->publishes([__DIR__ . '/../config/laraext.php' => config_path('laraext.php')], 'config');
        $this->app->bindIf('laraext.toolkit', function($app){
            return new Toolkit\Toolkit($app);
        }, true);
        $this->app->bindIf('laraext.cache', function($app){
            return new Cache\Cache($app);
        }, true);
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Laraext', Facades\LaraextToolkit :: class);
        $loader->alias('LaraextCache', Facades\LaraextCache :: class);
        $this->registerCommands();

    }


    protected function registerCommands()
    {
        $this->app->bindIf('command.laraext.db', function () {
            return new Console\DbToolsCommand;
        });

        $this->app->bindIf('command.laraext.jobs', function () {
            return new Console\JobsCommand;
        });

        $this->app->bindIf('command.laraext.logrotate', function () {
            return new Console\LogRotateCommand;
        });

        $this->commands(
            'command.laraext.db',
            'command.laraext.jobs',
            'command.laraext.logrotate'
        );
    }

    protected function registerCache()
    {
        $this->app['cache']->extend("memcached", function($app, $config){
            $prefix = $config['prefix'] ?? $app['config']['cache.prefix'];
            $memcached = $app['memcached.connector']->connect($config['servers']);
            return $app['cache']->repository(new MemcachedStore($memcached, $prefix));
        });


    }

    public function provides()
    {
        return [
            'command.laraext.db',
            'command.laraext.jobs',
            'command.laraext.logrotate'
        ];
    }
}