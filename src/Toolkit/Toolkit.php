<?php namespace Laraext\Toolkit;

class Toolkit
{
    protected $tools = [];
    protected $app;

    function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->app = $app;
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
                    var_dump($m);
                }
                $message->subject($subject);
            });
        }
    }



}