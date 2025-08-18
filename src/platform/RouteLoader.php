<?php

namespace PlatformBridge;

class RouteLoader
{
    /**
     * Loads all route configuration files from the specified directory.
     *
     * @param string $directory Path: platform/Native/src/Routes/Collections/
     * @return void
     */
    public static function load(string $directory): void
    {
        foreach (glob(rtrim($directory, '/') . '/*.php') as $file)
        {

            $callback = require $file;

            if (!$callback instanceof \Closure)
            {
                continue;
            }

            $callback();
        }
    }
}
