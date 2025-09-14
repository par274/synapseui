<?php

namespace NativePlatform\Adapters;

use PlatformBridge\BridgeConfig;

use InvalidArgumentException;

class AdapterManager
{
    protected BridgeConfig $config;
    private array $adapters = [];
    protected string $active;

    public function __construct(BridgeConfig $config, array $adapters, string $default = 'ollama')
    {
        $this->config = $config;
        $this->adapters = $adapters;

        if (!isset($adapters[$default]))
        {
            throw new InvalidArgumentException("LLM Adapter '{$default}' not found.");
        }

        $this->active = $default;
    }

    public function get()
    {
        if ($this->config->getLLMAdapterMethod() == 'client')
        {
            $this->adapters[$this->active]->setConfig([
                'base_url' => $this->config->getLLMAdapterUrl(),
                'api_key' => $this->config->getLLMAdapterApiKey()
            ]);
        }
        return $this->adapters[$this->active];
    }

    public function use(string $name): void
    {
        if (!isset($this->adapters[$name]))
        {
            throw new InvalidArgumentException("LLM Adapter '{$name}' not found.");
        }

        $this->active = $name;
    }

    public function is(string $name): bool
    {
        if ($this->active === $name)
        {
            return true;
        }

        return false;
    }

    public function setAdapter(string $key, callable $factory): void
    {
        $this->adapters[$key] = $factory;
    }
}
