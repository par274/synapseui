<?php

namespace PlatformBridge;

class RouteCollection
{
    protected static array $routes = [
        'index' => [
            'method' => ['GET'],
            'url' => '/',
            'handler' => ['IndexController', 'index']
        ]
    ];

    public static function get(string $routeName): array
    {
        return self::$routes[$routeName];
    }

    public static function has(string $routeName): bool
    {
        if (isset(self::$routes[$routeName]))
        {
            return true;
        }

        return false;
    }

    public static function all(): array
    {
        return self::$routes;
    }

    public static function set(string $name, string $url, string|array $method, array $handler): void
    {
        foreach (self::$routes as &$route)
        {
            if ($route[$name]['url'] === $url && $route[$name]['method'] === strtoupper($method))
            {
                $route[$name]['handler'] = $handler;
                return;
            }
        }

        self::$routes[$name] = [
            'method' => strtoupper($method),
            'url' => $url,
            'handler' => $handler
        ];
    }
}
