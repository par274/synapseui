<?php

namespace NativePlatform\SubContainer;

use Closure;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceContainer
{
    protected array $definitions = [];
    protected array $resolved = [];
    protected array $aliases = [];

    public function set(string $key, object|array $service): void
    {
        $this->definitions[$key] = $service;
    }

    public function alias(string $alias, string $to): void
    {
        $this->aliases[$alias] = $to;
    }

    public function get(string $key): mixed
    {
        $realKey = $this->aliases[$key] ?? $key;

        if (isset($this->resolved[$realKey]))
        {
            return $this->resolved[$realKey];
        }

        if (!isset($this->definitions[$realKey]))
        {
            throw new InvalidArgumentException("Service '{$realKey}' not found in container.");
        }

        $definition = $this->definitions[$realKey];

        if ($definition instanceof Closure)
        {
            $service = $definition($this);
        }
        else
        {
            $service = $definition;
        }

        $this->resolved[$realKey] = $service;

        return $service;
    }

    public function has(string $key): bool
    {
        $realKey = $this->aliases[$key] ?? $key;
        return isset($this->definitions[$realKey]);
    }

    public function lazy(string $key, ?Closure $factory = null): void
    {
        if ($factory === null)
        {
            $factory = match ($key)
            {
                'app:request'  => fn() => Request::createFromGlobals(),
                'app:response' => fn() => new Response(),
                default    => throw new InvalidArgumentException("No default factory defined for key: {$key}"),
            };
        }

        $this->definitions[$key] = $factory;
        unset($this->resolved[$key]);
    }

    public function all(?string $prefix = null): array
    {
        $keys = array_keys($this->definitions + $this->resolved);

        if ($prefix === null)
        {
            return array_intersect_key(
                $this->definitions + $this->resolved,
                array_flip($keys)
            );
        }

        $filter = fn(string $key): bool => str_starts_with($key, $prefix . ':');

        $filteredKeys = array_filter($keys, $filter);

        return array_intersect_key(
            $this->definitions + $this->resolved,
            array_flip($filteredKeys)
        );
    }
}
