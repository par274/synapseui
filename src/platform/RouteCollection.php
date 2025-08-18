<?php

declare(strict_types=1);

namespace PlatformBridge;

use Closure;
use InvalidArgumentException;
use FastRoute\RouteCollector;

class RouteCollection
{
    private static array $routes = [];

    private static string $prefix = '';

    /**
     * Registers a new route with the given name, methods, path, and handler.
     *
     * @param string       $name    The unique name of the route
     * @param array|string $methods HTTP methods that this route accepts (GET, POST, etc.)
     * @param string       $path    The URL path for the route
     * @param mixed        $handler The handler for the route (can be Closure or array)
     * @return void
     */
    public static function register(
        string       $name,
        array|string $methods,
        string       $path,
        mixed        $handler // Closure|array
    ): void
    {
        if ($path == '/' && self::$prefix !== '')
        {
            $path = rtrim($path, '/');
        }

        $fullPath = self::$prefix . $path;

        if (isset(self::$routes[$name]))
        {
            throw new InvalidArgumentException("Route '{$name}' already exists.");
        }

        $methods = array_map('strtoupper', (array) $methods);

        self::$routes[$name] = [
            'methods' => $methods,
            'path'    => $fullPath,
            'handler' => $handler,
        ];
    }

    /**
     * Sets a prefix for all subsequent route registrations within the callback.
     *
     * @param string  $prefix   The prefix to be applied to routes
     * @param Closure $callback Callback function that contains route definitions
     * @return void
     */
    public static function prefix(string $prefix, Closure $callback): void
    {
        $prev = self::$prefix;
        self::$prefix = rtrim($prev . '/' . trim($prefix, '/'), '/');

        $callback(new self());

        self::$prefix = $prev;
    }

    /**
     * Loads all registered routes into the provided RouteCollector instance.
     *
     * @param RouteCollector $collector The route collector to load routes into
     * @return void
     */
    public static function loadInto(RouteCollector $collector): void
    {
        foreach (self::$routes as $route)
        {
            $payload = $route['handler'];

            if (\count($route['methods']) === 1)
            {
                $collector->addRoute(
                    $route['methods'][0],
                    $route['path'],
                    $payload
                );
            }
            else
            {
                foreach ($route['methods'] as $method)
                {
                    $collector->addRoute($method, $route['path'], $payload);
                }
            }
        }
    }

    /**
     * Returns all registered routes.
     *
     * @return array All registered routes
     */
    public static function all(): array
    {
        return self::$routes;
    }
}
