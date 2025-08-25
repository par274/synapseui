<?php

namespace NativePlatform\SubContainer;

use Closure;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceContainer
{
    /**
     * @var array<string, object|array|Closure>
     */
    protected array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    protected array $resolved = [];

    /**
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Registers a service in the container.
     *
     * Supports lazy instantiation. Returns a closure producing an anonymous service
     * implementing `ServiceInterface`, allowing you to call `bootstrap()` or other
     * lazy methods.
     *
     * Example:
     * ```php
     * $factory = $container->set('myService', []);
     * $service = $factory();
     * $service->bootstrap();
     * ```
     *
     * @param string|array $key
     * @param object|array $service
     * @return Closure(): ServiceInterface Lazy factory closure
     */
    public function set(string|array $keys, object|array $service): Closure|array
    {
        $keys = (array) $keys;

        $factories = [];

        foreach ($keys as $key)
        {
            if (is_array($service))
            {
                // [ClassName::class, ...args]
                $this->definitions[$key] = function (ServiceContainer $c) use ($service, $key)
                {
                    $class = array_shift($service);
                    return new $class(...$service);
                };
            }
            elseif ($service instanceof Closure)
            {
                $this->definitions[$key] = function (ServiceContainer $c) use ($service, $key)
                {
                    return $service($c, $key);
                };
            }
            else
            {
                $this->definitions[$key] = $service;
            }

            /** @var callable $getFn */
            $getFn = fn() => $this->get($key);
            $factories[$key] = function () use ($getFn)
            {
                return new class($getFn) implements ServiceInterface {
                    private $getFn;
                    public function __construct(callable $getFn)
                    {
                        $this->getFn = $getFn;
                    }
                    public function bootstrap(): void
                    {
                        ($this->getFn)();
                    }
                };
            };
        }

        return count($factories) === 1 ? reset($factories) : $factories;
    }

    /**
     * Creates an alias for a service key.
     *
     * Aliases allow multiple keys to refer to the same service definition.
     *
     * @param string $alias
     * @param string $to The existing service key
     */
    public function alias(string $alias, string $to): void
    {
        $this->aliases[$alias] = $to;
    }

    /**
     * Retrieves a service instance from the container.
     *
     * If the service has been resolved previously, it returns the cached instance.
     * If the service definition is a closure, it is executed lazily.
     *
     * @param string $key
     * @return mixed The resolved service instance
     * @throws InvalidArgumentException If the service is not defined
     */
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

    /**
     * Checks if a service is defined in the container.
     *
     * @param string $key
     * @return bool True if the service exists, false otherwise
     */
    public function has(string $key): bool
    {
        $realKey = $this->aliases[$key] ?? $key;
        return isset($this->definitions[$realKey]);
    }

    /**
     * Registers a lazy service with an optional custom factory closure.
     *
     * If no factory is provided, default factories are used for predefined keys:
     * - 'app:request'  => Symfony Request from globals
     * - 'app:response' => Symfony Response
     *
     * @param string $key
     * @param Closure|null $factory Optional factory closure
     * @throws InvalidArgumentException If no default factory exists for the key
     */
    public function lazy(string $key, ?Closure $factory = null): void
    {
        if ($factory === null)
        {
            $factory = match ($key)
            {
                'app:request'  => fn() => Request::createFromGlobals(),
                'app:response' => fn() => new Response(),
                default => throw new InvalidArgumentException("No default factory defined for key: {$key}"),
            };
        }

        $this->definitions[$key] = $factory;
        unset($this->resolved[$key]);
    }

    /**
     * Returns all service definitions and resolved instances.
     *
     * Optionally filters services by prefix.
     *
     * @param string|null $prefix  Optional prefix to filter keys
     * @return array  Array of key => service
     */
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

/**
 * Marker interface for anonymous service objects created by the container.
 *
 * Any lazy service returned by `ServiceContainer::set()` implements this
 * interface, allowing IDEs to provide autocomplete for `bootstrap()` and
 * other lazy methods.
 */
interface ServiceInterface
{
    /**
     * Bootstraps the service.
     *
     * This method should initialize the service when needed.
     */
    public function bootstrap(): void;
}
