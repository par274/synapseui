<?php

namespace NativePlatform\Templater\Filter;

class FormattingFilter
{
    public static function compileCap(string $expr): string
    {
        return "\$this->echoText(\mb_strtoupper({$expr}));";
    }

    public static function compileLower(string $expr): string
    {
        return "\$this->echoText(\mb_strtolower({$expr}));";
    }

    public static function compileUpperFirst(string $expr): string
    {
        return "\$this->echoText(\ucfirst({$expr}));";
    }
}
