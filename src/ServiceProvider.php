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

    }


    public function provides()
    {
        return array();
    }
}