<?php namespace Laraext;

use Illuminate\Support\ServiceProvider as LServiceProvider;

class ServiceProvider extends LServiceProvider {


    public function boot()
    {
    }

    public function register()
    {
        $this->publishes([__DIR__ . '/../config/laraext.php' => config_path('laraext.php')], 'config');
//        $configPath = __DIR__ . '/../config/laraext.php';
//        $this->publishes([$configPath => config_path('laraext.php')], 'config');
//        $this->mergeConfigFrom(__DIR__ . '../config/laraext.php', 'laraext');
//        $this->publishes([__DIR__ . '/../public/' => public_path() . "/vendor/crud/"], 'assets');
//        $this->publishes([__DIR__ . '/../database/' => base_path("database")], 'database');
//
//        $this->app->singleton('CmsHelper',function()
//        {
//            return new \LaravelCrud\Helper\CmsHelper(\Auth::user());
//        });
//
//        $this->app->singleton('CrudHelper',function()
//        {
//            return new \LaravelCrud\Helper\CrudHelper();
//        });

    }


    public function provides()
    {
        return array();
    }
}