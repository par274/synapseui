<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;

/**
 * Represents a plain text segment in the template.
 *
 * Example:
 *     Hello, this is static text.
 *
 * This node does not contain variables or tags.
 */
class TextNode implements Node
{
    protected string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();
        $escaped = preg_replace("/'/", "\\'", $this->text);
        return sprintf($tpl['text'], $escaped);
    }

    protected function getTemplates(): array
    {
        return [
            'text' => "\$this->echoRaw('%s');"
        ];
    }

    public function toArray(): array
    {
        return ['type' => TAG::T_TEXT, 'value' => $this->text];
    }

    public function getType(): string
    {
        return TAG::T_TEXT;
    }
}
