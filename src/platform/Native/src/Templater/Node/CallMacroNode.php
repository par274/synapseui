<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Expr\ExprTransformer;

/**
 * Represents a macro call using <sx:call>.
 *
 * Syntax:
 *     <sx:call macro="userCard" user="$user" />
 *
 * This node resolves the named macro at compile-time and compiles
 * a direct PHP method call to the generated macro function.
 *
 * Each argument is parsed as an expression and passed positionally
 * to the macro's compiled method.
 */
class CallMacroNode implements Node
{
    protected string $macroName;
    protected array $args;

    public function __construct(string $macroName, array $args = [])
    {
        $this->macroName = $macroName;
        $this->args = $args;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();
        $method = 'macro_' . md5($this->macroName);

        $compiledArgs = array_map(
            fn($arg) => ExprTransformer::transformArg($arg),
            array_values($this->args)
        );

        $argsString = implode(', ', $compiledArgs);

        return sprintf($tpl['call'], $method, $argsString);
    }

    protected function getTemplates(): array
    {
        return [
            'call' => <<<PHP
\$this->%s(%s);
PHP
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_CALL,
            'macro' => $this->macroName,
            'args' => $this->args
        ];
    }

    public function getType(): string
    {
        return TAG::T_CALL;
    }
}
