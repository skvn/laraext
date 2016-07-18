<?php namespace Skvn\Laraext\Toolkit;


class Toolkit
{
    protected $tools = [];
    protected $instances = [];
    protected $app;

    function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->app = $app;
        foreach ($this->app['config']->get('laraext.tools') as $tool)
        {
            if (is_array($tool) || strpos($tool, '@') !== false)
            {
                $this->addTool($tool);
            }
            else
            {
                $this->addToolset($tool);
            }
        }
    }

    function sendNotify($notify, $subject = null)
    {
        if ($this->app['config']->get('laraext.notify_email'))
        {
            if (is_null($subject))
            {
                $subject = $notify;
            }
            $to = $this->app['config']->get('laraext.notify_email');
            $this->app['mailer']->raw($notify, function($message) use($subject, $to){
                foreach (explode(",", $to) as $m)
                {
                    $message->to($m);
                }
                $message->subject($subject);
            });
        }
    }

    function addTool($name, Callable $tool = null)
    {
        if (strpos($name, '@') !== false)
        {
            list($cls, $meth) = explode('@', $name);
            $name = $meth;
            $tool = [$this->getInstance($cls), $meth];
        }
        $this->tools[$name] = $tool;
    }

    function addToolset($class)
    {
        foreach (get_class_methods($class) as $meth)
        {
            if (strpos($meth, '_') !== 0)
            {
                $this->addTool($meth, [$this->getInstance($class), $meth]);
            }
        }
    }

    function getInstance($cls)
    {
        if (!isset($this->instances[$cls]))
        {
            $this->instances[$cls] = $this->app->make($cls);
        }
        return $this->instances[$cls];
    }

    function __call($method, $args)
    {
        if (array_key_exists($method, $this->tools))
        {
            return call_user_func_array($this->tools[$method], $args);
        }
        throw new \Exception("Method " . $method . " not found in Laraext Toolkit");
    }




}


