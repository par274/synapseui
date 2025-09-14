<?php

namespace NativePlatform\SubContainer\Style;

use PlatformBridge\BridgeConfig;
use NativePlatform\SubContainer\Style\UiInterface;

use InvalidArgumentException;

class UiManager
{
    protected BridgeConfig $config;
    protected array $drivers = [];
    protected string $active;

    public function __construct(BridgeConfig $config, array $drivers, string $default = 'daisyui')
    {
        $this->config = $config;
        $this->drivers = $drivers;

        if (!isset($drivers[$default]))
        {
            throw new InvalidArgumentException("UI driver '{$default}' not found.");
        }

        $this->active = $default;
    }

    public function use(string $name): void
    {
        if (!isset($this->drivers[$name]))
        {
            throw new InvalidArgumentException("UI driver '{$name}' not found.");
        }

        $this->active = $name;
    }

    /**
     * @return UiInterface
     */
    public function get(): UiInterface
    {
        $this->drivers[$this->active]->setConfig($this->config);
        return $this->drivers[$this->active];
    }

    public function driver(): UiInterface
    {
        return $this->drivers[$this->active];
    }

    public function has(string $name): bool
    {
        return isset($this->drivers[$name]);
    }
}
