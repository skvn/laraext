<?php

namespace Skvn\Laraext\Validation;

use Illuminate\Validation\ValidationServiceProvider as LaravelProvider;


class ValidationServiceProvider extends LaravelProvider
{

    function register()
    {
        parent :: register();
        $class = $this->app['config']['laraext.validator_class'];
        $this->app['validator']->resolver(function($translator, $data, $rules, $messages = [], $customAttributes = []) use ($class){
            return new $class($translator, $data, $rules, $messages, $customAttributes);
        });
    }

}
