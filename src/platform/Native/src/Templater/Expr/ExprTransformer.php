<?php

namespace NativePlatform\Templater\Expr;

use NativePlatform\Templater\SymbolTable;

class ExprTransformer
{
    public static ?SymbolTable $symbols = null;

    /**
     * Transforms variable expressions like $foo.bar or $foo->bar.baz
     * into safe PHP code that supports both arrays and objects.
     */
    public static function transformVar(string $var): string
    {
        $normalized = str_replace('->', '.', ltrim($var, '$'));
        $parts = explode('.', $normalized);
        $root = $parts[0];

        if (self::$symbols && self::$symbols->isLocal('$' . $root))
        {
            if (count($parts) === 1)
            {
                return '$' . $root;
            }

            return '$' . $root . implode('', array_map(
                fn($p) => "['$p']",
                array_slice($parts, 1)
            ));
        }

        return "\$this->contextGetStrict(\$this->context, " . var_export($parts, true) . ")";
    }

    public static function transformArg(string $arg): string
    {
        $arg = trim($arg);

        if (is_numeric($arg)) return $arg;

        if ((str_starts_with($arg, '"') && str_ends_with($arg, '"')) ||
            (str_starts_with($arg, "'") && str_ends_with($arg, "'"))
        )
        {
            return $arg;
        }

        if (str_starts_with($arg, '$'))
        {
            return self::transformVar($arg);
        }

        if ($arg === '[]' || str_starts_with($arg, '['))
        {
            return $arg;
        }

        return var_export($arg, true);
    }

    /**
     * Transforms a string with interpolation like "Welcome {$user.name}" into
     * PHP concatenation: 'Welcome ' . \$this->context['user']['name']
     */
    public static function transformString(string $value): string
    {
        // Split on interpolation tokens
        $parts = preg_split(
            '/(\{\s*\$[a-zA-Z_][a-zA-Z0-9_\.]*\s*\})/',
            $value,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        $codeParts = [];
        foreach ($parts as $part)
        {
            if (preg_match('/^\{\s*(\$[a-zA-Z_][a-zA-Z0-9_\.]*)\s*\}$/', $part, $m))
            {
                // interpolation variable
                $codeParts[] = self::transformVar($m[1]);
            }
            else
            {
                // literal text, escape single quotes
                $escaped = str_replace("'", "\\'", $part);
                $codeParts[] = "'{$escaped}'";
            }
        }
        return implode(' . ', $codeParts);
    }

    /**
     * Transforms a general expression, handling variables and literals.
     */
    public static function transformExpr(string $expr): string
    {
        if (str_contains($expr, '{'))
        {
            return self::transformString($expr);
        }

        $expr = preg_replace_callback(
            '/(!?\s*)(isset|empty|is_null)\((.+?)\)/',
            function ($m)
            {
                $negation = trim($m[1]);
                $func = strtolower($m[2]);
                $inner = trim($m[3]);

                $innerTransformed = self::safeVar($inner);

                return match ($func)
                {
                    'isset'   => $negation === '!' ? "null === {$innerTransformed}" : "null !== {$innerTransformed}",
                    'empty'   => $negation === '!' ? $innerTransformed : "!({$innerTransformed})",
                    'is_null' => $negation === '!' ? "null !== {$innerTransformed}" : "null === {$innerTransformed}",
                    default   => $m[0],
                };
            },
            $expr
        );

        return preg_replace_callback(
            '/\$[a-zA-Z_][a-zA-Z0-9_\.]*/',
            fn($matches) => self::transformVar($matches[0]),
            $expr
        );
    }

    protected static function safeVar(string $var): string
    {
        $var = trim($var);

        if (str_starts_with($var, '$'))
        {
            $parts = explode('.', ltrim($var, '$'));
            $root = array_shift($parts);
            $access = implode('', array_map(fn($p) => "['$p']", $parts));
            return '$' . $root . $access;
        }

        return $var;
    }
}
