<?php namespace Skvn\Laraext\Console;

use Illuminate\Console\Command as LaravelCommand;

class LockableCommand extends LaravelCommand
{

    use LockableCommandTrait;


}
