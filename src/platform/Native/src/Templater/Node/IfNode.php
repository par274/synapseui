<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Expr\ExprTransformer;

/**
 * Represents a conditional <sx:if> block.
 *
 * Syntax:
 *     <sx:if is="$user.loggedIn">
 *         Hello, {$user.name}
 *     <sx:elseif is="$user.admin" />
 *         Admin access
 *     <sx:else />
 *         Please log in
 *     </sx:if>
 *
 * Supports nested content, elseif branches, and an optional else block.
 */
class IfNode implements Node
{
    protected string $condition;
    protected array $body = [];
    protected array $elseifBlocks = [];
    protected array $elseBody = [];

    public function __construct(string $condition)
    {
        $this->condition = $condition;
    }

    public function addChild(Node $node): void
    {
        $this->body[] = $node;
    }

    public function addElseif(string $condition, array $body): void
    {
        $this->elseifBlocks[] = ['condition' => $condition, 'body' => $body];
    }

    public function setElse(array $body): void
    {
        $this->elseBody = $body;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();

        $ifCondition = ExprTransformer::transformExpr($this->condition);
        $ifBody = implode("\n", array_map(fn($n) => $n->toPhp(), $this->body));
        $php = sprintf($tpl['if'], $ifCondition, $ifBody);

        foreach ($this->elseifBlocks as $elseif)
        {
            $cond = ExprTransformer::transformExpr($elseif['condition']);
            $body = implode("\n", array_map(fn($n) => $n->toPhp(), $elseif['body']));
            $php .= sprintf($tpl['elseif'], $cond, $body);
        }

        if (!empty($this->elseBody))
        {
            $body = implode("\n", array_map(fn($n) => $n->toPhp(), $this->elseBody));
            $php .= sprintf($tpl['else'], $body);
        }

        $php .= $tpl['end'];
        return $php;
    }

    protected function getTemplates(): array
    {
        return [
            'if'     => "if (%s):\n%s\n",
            'elseif' => "elseif (%s):\n%s\n",
            'else'   => "else:\n%s\n",
            'end'    => "endif;\n"
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_IF,
            'condition' => $this->condition,
            'body' => array_map(fn($n) => $n->toArray(), $this->body),
            'elseif' => array_map(fn($e) => [
                'condition' => $e['condition'],
                'body' => array_map(fn($n) => $n->toArray(), $e['body'])
            ], $this->elseifBlocks),
            'else' => array_map(fn($n) => $n->toArray(), $this->elseBody)
        ];
    }

    public function getType(): string
    {
        return TAG::T_IF;
    }
}
