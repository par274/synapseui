<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Engine;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Expr\ExprTransformer;
use NativePlatform\Templater\SymbolTable;

/**
 * Represents a variable expression like {$foo} or {$user.name}.
 *
 * The variable is resolved at runtime using the provided SymbolTable.
 * Supports dotted access and filters (if implemented).
 *
 * Example:
 *     {$user.name}
 *     {$foo|capitalize}
 *     {$foo->bar->baz}
 *     {$foo->bar($args)}
 *     {$foo.bar(args)}
 *     {$foo.bar()|ucwords} 
 *     Available filters: capitalize, lower, ucwords, raw, dump, sy-dump.
 */
class VariableNode implements Node
{
    protected Engine $engine;
    protected SymbolTable $symbols;
    protected string $var;
    protected bool $call;
    protected array $args = [];
    protected bool|string $filter = false;

    public function __construct(Engine $engine, SymbolTable $symbols, string $var, bool $call = false, array $args = [], bool|string $filter = false)
    {
        $this->engine = $engine;
        $this->symbols = $symbols;
        $this->var = trim($var);
        $this->call = $call;
        $this->args = $args;

        if ($filter)
        {
            $this->filter = $filter;
        }
    }

    public function toPhp(): string
    {
        $filter = 'html';

        // Parse filter
        $parts = explode('|', $this->var);
        $varPart = trim($parts[0]);

        if (isset($parts[1]))
        {
            $filter = trim($parts[1]);
        }

        // Dotted or object-style access
        if (str_contains($varPart, '.') || str_contains($varPart, '->'))
        {
            $accessExpr = ExprTransformer::transformVar($varPart, $this->engine);
        }
        else
        {
            $clean = ltrim($varPart, '$');
            $accessExpr = $this->symbols->isLocal("\${$clean}")
                ? '$' . $clean
                : "\$this->context['{$clean}']";
        }

        if ($this->call)
        {
            $compiledArgs = array_map(
                fn($arg) => ExprTransformer::transformArg($arg, $this->engine),
                $this->args
            );
            $argList = implode(', ', $compiledArgs);

            $fnVar = '$__fn_' . uniqid();
            $resVar = '$__res_' . uniqid();

            $code  = "{$fnVar} = {$accessExpr};\n";
            $code .= "{$resVar} = is_callable({$fnVar}) ? {$fnVar}({$argList}) : '';\n";

            if ($this->filter)
            {
                if (!$this->engine->filters->has($this->filter))
                {
                    throw new \Exception("{$this->filter} filter is not found. Please register it.");
                }

                $callable = $this->engine->filters->get($this->filter)['callable'];
                $filteredExpr = call_user_func($callable, $resVar);
                $code .= "{$filteredExpr};\n";
            }
            else
            {
                $code .= "\$this->echoText({$resVar});\n";
            }

            return $code;
        }

        $expr = "({$accessExpr} ?? '')";

        if (isset($parts[1]))
        {
            if (!$this->engine->filters->has($filter))
            {
                throw new \Exception("{$filter} filter is not found. Please register it.");
            }

            $callable = $this->engine->filters->get($filter)['callable'];
            return call_user_func($callable, $expr) . ";\n";
        }

        return "\$this->echoText({$expr});\n";
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_VAR,
            'var' => $this->var,
            'call' => $this->call,
            'args' => $this->args
        ];
    }

    public function getType(): string
    {
        return TAG::T_VAR;
    }
}
