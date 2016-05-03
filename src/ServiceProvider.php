<?php namespace Laraext;

use Illuminate\Support\ServiceProvider as LServiceProvider;

class ServiceProvider extends LServiceProvider {


    public function boot()
    {
    }

    public function register()
    {
        $this->publishes([__DIR__ . '/../config/laraext.php' => config_path('laraext.php')], 'config');
        $this->app->bindIf('laraext.toolkit', function($app){
            return new Toolkit\Toolkit($app);
        }, true);
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Laraext', Facades\LaraextToolkit :: class);
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



    public function provides()
    {
        return [
            'command.laraext.db',
            'command.laraext.jobs',
            'command.laraext.logrotate'
        ];
    }
}