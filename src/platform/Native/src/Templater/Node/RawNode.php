<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;

/**
 * Represents a raw output block using <sx:raw>.
 * Alternative you can use a filter(raw) for this instead.
 *
 * Syntax:
 *     <sx:raw>
 *         {$user.bio}
 *     </sx:raw>
 *
 * Output is not escaped. Useful for rendering trusted HTML content.
 */
class RawNode implements Node
{
    protected array $body = [];

    public function __construct(array $body = [])
    {
        $this->body = $body;
    }

    public function addChild(Node $node): void
    {
        $this->body[] = $node;
    }

    public function toPhp(): string
    {
        $php = '';
        foreach ($this->body as $node)
        {
            $php .= str_replace('echoText', 'echoRaw', $node->toPhp()) . "\n";
        }
        return $php;
    }

    public function toArray(): array
    {
        return [
            'type' => 'raw',
            'body' => array_map(fn($n) => $n->toArray(), $this->body)
        ];
    }

    public function getType(): string
    {
        return TAG::T_RAW;
    }
}
