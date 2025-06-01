<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;

/**
 * Represents a non-rendered comment block using <sx:comment>.
 *
 * Syntax:
 *     <sx:comment>This will not be output</sx:comment>
 *
 * Entire content is ignored during compilation and output.
 */
class CommentNode implements Node
{
    public function toPhp(): string
    {
        // This node does not produce any PHP output
        return '';
    }

    public function toArray(): array
    {
        return ['type' => 'comment'];
    }

    public function getType(): string
    {
        return TAG::T_COMMENT;
    }
}
