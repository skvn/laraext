<?php namespace Skvn\Laraext\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as LaravelHandler;
use Exception;

class Handler extends LaravelHandler {


    public function report(Exception $e)
    {
        foreach ($this->dontReport as $type)
        {
            if ($e instanceof $type)
                return;
        }
        if (in_array(class_basename($e), \Config :: get('laraext.errors.skip_log_exceptions')))
        {
            return;
        }

        $this->log->error($e, ['exception' => $e]);
    }


}
