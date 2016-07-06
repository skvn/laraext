<?php namespace Laraext\Cache;

use Memcached;
use Illuminate\Cache\MemcachedStore as LaravelStore;

/**
 *
 * memcached git version
 */

class MemcachedStore extends LaravelStore
{
    public function many(array $keys)
    {
        $prefixedKeys = array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);

        $values = $this->memcached->getMulti($prefixedKeys, Memcached::GET_PRESERVE_ORDER);

        if ($this->memcached->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }

        return array_combine($keys, $values);
    }

}