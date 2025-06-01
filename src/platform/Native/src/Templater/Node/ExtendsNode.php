<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;

/**
 * Represents a template inheritance using <sx:extends>.
 *
 * Syntax:
 *     <sx:extends template="base.tpl" />
 *
 * Must be declared at the top-level of the template.
 * Enables layout inheritance and block replacement.
 */
class ExtendsNode implements Node
{
    protected string $templateName;

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

    public function getTemplate(): string
    {
        return $this->templateName;
    }

    public function toPhp(): string
    {
        return '';
    }

    public function toArray(): array
    {
        return [
            'type' => 'extends',
            'template' => $this->templateName
        ];
    }

    public function getType(): string
    {
        return TAG::T_EXTENDS;
    }
}
