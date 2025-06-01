<?php

namespace NativePlatform\Templater;

class SymbolTable
{
    protected array $symbols = [];

    public function define(string $name, string $type = 'local'): void
    {
        $this->symbols[$name] = $type;
    }

    public function isLocal(string $name): bool
    {
        return isset($this->symbols[$name]) && $this->symbols[$name] === 'local';
    }
}
