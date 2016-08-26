<?php namespace Skvn\Laraext\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadConfiguration as LaravelLoadConfiguration;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Config\Repository as RepositoryContract;


class LoadConfiguration extends LaravelLoadConfiguration
{
    protected function getConfigurationFiles(Application $app)
    {

        $files = [];

        $configPath = realpath($app->configPath());

        $ondemand = explode(",", env('CONF_ONDEMAND', ""));


        foreach (Finder::create()->files()->name('*.*')->in($configPath) as $file) {
            $nesting = $this->getConfigurationNesting($file, $configPath);
            $ext = array_pop(explode('.', $file));

            $key = $nesting.basename($file->getRealPath(), '.' . $ext);
            if (in_array($key, $ondemand))
            {
                continue;
            }
            $files[$key] = $file->getRealPath();
        }

        return $files;
    }


    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        foreach ($this->getConfigurationFiles($app) as $key => $path)
        {
            $ext = array_pop(explode(".", $path));
            switch ($ext)
            {
                case 'php':
                    $repository->set($key, require $path);
                break;
                case 'ini':
                    $repository->set($key, parse_ini_file($path, true));
                break;
            }
        }
    }


}