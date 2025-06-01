<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Expr\ExprTransformer;

/**
 * Represents a variable assignment using <sx:set>.
 *
 * Syntax:
 *     <sx:set var="$welcome" value="Welcome {$user.name}" />
 *     {$welcome}
 *
 * Variables defined here are stored in the local SymbolTable.
 */
class SetNode implements Node
{
    protected string $var;
    protected string $value;

    public function __construct(string $var, string $value)
    {
        $this->var = ltrim($var, '$');
        $this->value = $value;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();
        $expr = ExprTransformer::transformExpr($this->value);

        return sprintf($tpl['set'], $this->var, $expr);
    }

    protected function getTemplates(): array
    {
        return [
            'set' => "\$this->context['%s'] = %s;"
        ];
    }

    public function toArray(): array
    {
        return [
            'type'  => TAG::T_SET,
            'var'   => $this->var,
            'value' => $this->value
        ];
    }

    public function getType(): string
    {
        return TAG::T_SET;
    }
}
