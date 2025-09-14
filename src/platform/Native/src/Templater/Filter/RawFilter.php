<?php

namespace NativePlatform\Templater\Filter;

class RawFilter
{
    public static function compile(string $expr): string
    {
        return "\$this->echoRaw({$expr});";
    }
}
