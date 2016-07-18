<?php namespace Skvn\Laraext\Cache;

use Memcache;


class Cache
{

    function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->app = $app;
    }


    public function put($key, $value, $minutes, $tags = null)
    {
        return $this->app['cache']->put($key, $this->prepareValue($value, $tags), $minutes);
    }

    public function add($key, $value, $minutes, $tags = null)
    {
        return $this->app['cache']->add($key, $this->prepareValue($value, $tags), $minutes);
    }

    public function forever($key, $value, $tags = null)
    {
        $this->put($key, $value, 0, $tags);
    }

    function forgetTag($tags)
    {
        if (!is_array($tags))
        {
            $tags = [$tags];
        }
        $tags = array_map(function($v){return $v . "::tag";}, $tags);
        foreach ($tags as $tag)
        {
            if (!$this->app['cache']->increment($tag))
            {
                $this->add($tag, 0, 0);
                $this->app['cache']->increment($tag);
            }
        }
    }

    public function get($key)
    {
        $value = $this->app['cache']->get($key);

        if (is_array($value) && isset($value['value']) && isset($value['___tags']))
        {
            $tags = $this->app['cache']->many(array_keys($value['___tags']));
            foreach ($tags as $k => $v)
            {
                if (is_null($v) || $tags[$k] != $value['___tags'][$k])
                {
                    $this->app['cache']->forget($key);
                    return null;
                }
            }
            return $value['value'];
        }
        return $value;
    }

    protected function prepareValue($value, $tags)
    {
        if (empty($tags))
        {
            return $value;
        }
        if (!is_array($tags))
        {
            $tags = [$tags];
        }
        $tags = array_map(function($v){return $v . "::tag";}, $tags);
        $tags = $this->app['cache']->many($tags);
        foreach ($tags as $k => $v)
        {
            if (is_null($v))
            {
                $this->app['cache']->add($k, 0, 0);
                $tags[$k] = 0;
            }
        }
        return ['value' => $value, '___tags' => $tags];
    }


    function __call($method, $args)
    {
        return call_user_func_array([$this->app['cache'], $method], $args);
    }

    function dumpMemcacheKeys($args = array())
    {
        if (!class_exists("Memcache"))
        {
            throw new \Exception("For key dumping Memcache extension is required");
        }

        $memcache = new Memcache();
        $memcache->connect(env('MEMCACHED_HOST', '127.0.0.1'), env('MEMCACHED_PORT', 11211));

        $slabs = $memcache->getStats("slabs");
        $keys = array();
        foreach ($slabs as $slabid => $slab)
        {
            if (!is_numeric($slabid))
            {
                continue;
            }
            $content = $memcache->getStats("cachedump", intval($slabid), 1000000);
            if (is_array($content))
            {
                foreach ($content as $key => $info)
                {
                    if (isset($args['pattern']))
                    {
                        if (!preg_match("#" . $args['pattern'] . "#", $key))
                        {
                            continue;
                        }
                    }
                    if (isset($args['use_prefix']))
                    {
                        if (!preg_match("#^".$this->prefix.":#", $key))
                        {
                            continue;
                        }
                    }
                    $keys[] = $key;
                }
            }
        }
        if (isset($args['sort']))
        {
            sort($keys);
        }
        return $keys;
    }



}


