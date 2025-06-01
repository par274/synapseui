<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Node\MacroNode;

/**
 * Represents a named content block using <sx:block>.
 * If this node is to be defined, it must be used with extends.
 *
 * Used in conjunction with <sx:extends> to define overridable sections.
 *
 * Syntax:
 *     <sx:block name="content">
 *         This is the main content.
 *     </sx:block>
 */
class BlockNode implements Node
{
    protected string $name;
    protected array $body;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->body = [];
    }

    public function addChild(Node $node): void
    {
        $this->body[] = $node;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();

        $method = 'renderBlock_' . md5($this->name);

        // Generate block body with indentation
        $bodyPhp = '';
        foreach ($this->body as $node)
        {
            if (!($node instanceof MacroNode))
            {
                $line = rtrim($node->toPhp());
                $bodyPhp .= str_replace("\n", "\n", $line) . "\n";
            }
        }

        $renderMethod = sprintf($tpl['render'], $this->name, $method, $bodyPhp);
        $callbackGetter = sprintf($tpl['getter'], $this->name, $method);

        return $renderMethod . "\n" . $callbackGetter;
    }

    protected function getTemplates(): array
    {
        return [
            'render' => <<<PHP
    // Block '%s' definition
    protected function %s(): void {
%s    }
PHP,
            'getter' => <<<PHP
    public function getBlockCallback_%s(): callable {
        return [\$this, '%s'];
    }
PHP
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => 'block',
            'name' => $this->name,
            'body' => array_map(fn(Node $n) => $n->toArray(), $this->body)
        ];
    }

    public function getType(): string
    {
        return TAG::T_BLOCK;
    }
}
