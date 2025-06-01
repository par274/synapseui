<?php

namespace PlatformBridge;

class BridgeConfig
{
    private array $startWith = ['APP'];

    protected array $env = [];
    protected array $isolated = [];

    public function __construct(array $env)
    {
        foreach ($env as $key => $value)
        {
            foreach ($this->startWith as $start)
            {
                if (str_starts_with($key, $start . '_'))
                {
                    $this->env[$key] = $value;
                }
            }
        }
    }

    public function isolate(array $starts)
    {
        $env = $this->env;
        $this->env = [];
        foreach ($env as $key => $value)
        {
            foreach ($starts as $start)
            {
                if (str_starts_with($key, $start . '_'))
                {
                    $this->env[$key] = $value;
                }
            }
            $this->isolated[$key] = $value;
        }
    }

    public function endIsolate(): void
    {
        self::__construct($this->isolated);
        $this->isolated = [];
    }

    public function all(): array
    {
        return $this->env;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->env[$key] ?? $default;
    }

    public function set(string $key, string $value): void
    {
        $this->env[$key] = $value;
    }

    public function getSecret(): string
    {
        return $this->get('APP_SECRET', 'default-secret');
    }

    public function getAppUrl(): string
    {
        return rtrim($this->get('APP_URL', 'http://localhost'), '/');
    }

    public function getEnv(): string
    {
        return $this->get('APP_ENV', 'prod');
    }

    public function getLLMAdapterMethod(): string
    {
        if ($this->get('APP_LLM_ADAPTER_METHOD') !== 'client' && $this->get('APP_LLM_ADAPTER_METHOD') !== 'server')
        {
            $this->env['APP_LLM_ADAPTER_METHOD'] = 'client';
        }

        return $this->get('APP_LLM_ADAPTER_METHOD', 'client');
    }

    public function getLLMAdapter(): string
    {
        return $this->get('APP_LLM_ADAPTER', 'ollama');
    }

    public function getLLMAdapterUrl(): string
    {
        return $this->get('APP_LLM_ADAPTER_URL', 'http://localhost:11434');
    }

    public function getLLMAdapterApiKey(): string|null
    {
        return $this->get('APP_LLM_ADAPTER_API_KEY', null);
    }

    public function getDatabaseParams(): array
    {
        return [
            'driver'   => $this->get('APP_DB_DRIVER'),
            'host'     => $this->get('APP_DB_HOST'),
            'port'     => $this->get('APP_DB_PORT'),
            'dbname'   => $this->get('APP_DB_NAME'),
            'user'     => $this->get('APP_DB_USER'),
            'password' => $this->get('APP_DB_PASS'),
        ];
    }

    public function getRouteCollection(): array
    {
        return RouteCollection::all();
    }

    public function asset(string $path): string
    {
        $base = '/' . trim($this->get('APP_ASSET_PATH') ?? 'assets', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
