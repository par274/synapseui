<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;

/**
 * Represents a macro definition using <sx:macro>.
 *
 * Syntax:
 *     <sx:macro name="userCard" user="$user">
 *         <div>{$user.name}</div>
 *     </sx:macro>
 *
 * Macros are reusable template fragments with named parameters.
 * Parameters are defined as attributes (excluding 'name') and become
 * function arguments during compilation.
 *
 * Example usage via <sx:call>:
 *     <sx:call macro="userCard" user="$someUser" />
 *
 * The macro body is compiled into a protected PHP method.
 */
class MacroNode implements Node
{
    protected string $name;
    protected array $args;
    protected array $body;

    public function __construct(string $name, array $args = [], array $body = [])
    {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();
        $method = 'macro_' . md5($this->name);

        $argList = implode(', ', array_map(
            fn($arg) => '$' . ltrim($arg['name'], '$'),
            $this->args
        ));

        $bodyPhp = '';
        foreach ($this->body as $node)
        {
            $line = rtrim($node->toPhp());
            $bodyPhp .= str_replace("\n", "\n        ", $line) . "\n";
        }

        return sprintf($tpl['macro'], $this->name, $method, $argList, $bodyPhp);
    }

    protected function getTemplates(): array
    {
        return [
            'macro' => <<<PHP
    // Macro '%s'
    protected function %s(%s): void
    {
        %s
    }
PHP
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_MACRO,
            'name' => $this->name,
            'args' => $this->args,
            'body' => array_map(fn($n) => $n->toArray(), $this->body)
        ];
    }

    public function getType(): string
    {
        return TAG::T_MACRO;
    }
}
