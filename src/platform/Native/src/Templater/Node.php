<?php

namespace NativePlatform\Templater;

interface Node
{
    public function getType(): string;
    public function toPhp(): string;
    public function toArray(): array;
}
