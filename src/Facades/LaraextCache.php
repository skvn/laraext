<?php namespace Laraext\Facades;

use Illuminate\Support\Facades\Facade;

class LaraextCache extends Facade
{
    protected static function getFacadeAccessor() { return 'laraext.cache'; }
}