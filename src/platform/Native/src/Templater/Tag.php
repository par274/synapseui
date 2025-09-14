<?php

namespace NativePlatform\Templater;

class Tag
{
    protected static string $prefix;

    public const T_TEXT = 'text';

    public const T_VAR = 'variable';

    public const T_IF = 'if';
    public const T_ELSEIF = 'elseif';
    public const T_ELSE = 'else';

    public const T_FOREACH = 'foreach';

    public const T_BLOCK = 'block';
    public const T_EXTENDS = 'extends';

    public const T_INCLUDE = 'include';

    public const T_RAW = 'raw';

    public const T_SET = 'set';

    public const T_COMMENT = 'comment';

    public const T_MACRO = 'macro';
    public const T_CALL = 'call';

    public const T_UI = 'ui';

    public const LIST_ALL = [
        self::T_TEXT,
        self::T_VAR,
        self::T_IF,
        self::T_ELSEIF,
        self::T_ELSE,
        self::T_FOREACH,
        self::T_BLOCK,
        self::T_EXTENDS,
        self::T_INCLUDE,
        self::T_RAW,
        self::T_SET,
        self::T_COMMENT,
        self::T_MACRO,
        self::T_CALL,
        self::T_UI
    ];

    public static function get(string $tag, string|bool $prefix = false): bool|string
    {
        if (!self::has($tag))
        {
            return false;
        }

        if ($prefix)
        {
            self::$prefix = $prefix;
        }

        $constName = __CLASS__ . '::T_' . strtoupper($tag);

        if (!defined($constName))
        {
            throw new \InvalidArgumentException("Tag constant T_" . strtoupper($tag) . " not found.");
        }

        return self::$prefix . ":" . constant($constName);
    }

    public static function has(string $tag): bool
    {
        if (!in_array($tag, self::LIST_ALL))
        {
            return false;
        }

        return true;
    }

    public static function setPrefix(string $prefix)
    {
        self::$prefix = $prefix;
    }
}
