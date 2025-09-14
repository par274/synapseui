<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Expr\ExprTransformer;
use NativePlatform\Templater\SymbolTable;

/**
 * Represents a loop structure defined by <sx:foreach>.
 *
 * Syntax:
 *     <sx:foreach loop="$items" key="$i" value="$item">
 *         {$i}: {$item}
 *     </sx:foreach>
 *
 * Both key and value are optional; if key is omitted, a numeric index is assumed.
 */
class ForeachNode implements Node
{
    protected SymbolTable $symbols;
    protected string $loop;
    protected ?string $key;
    protected string $value;
    protected array $body = [];

    public function __construct(SymbolTable $symbols, string $loop, string $value, ?string $key = null)
    {
        $this->symbols = $symbols;
        $this->loop = $loop;
        $this->value = $value;
        $this->key = $key;
    }

    public function addChild(Node $node): void
    {
        $this->body[] = $node;
    }

    public function toPhp(): string
    {
        ExprTransformer::$symbols = $this->symbols;
        
        $tpl = $this->getTemplates();

        $loopExpr = ExprTransformer::transformExpr($this->loop);
        $keyPart = $this->key ? '$' . ltrim($this->key, '$') . ' => ' : '';
        $keyVar = $this->key ? '$' . ltrim($this->key, '$') : null;
        $valueVar = '$' . ltrim($this->value, '$');

        if ($keyVar)
        {
            $this->symbols->define($keyVar);
        }
        $this->symbols->define($valueVar);

        $body = implode("\n", array_map(fn(Node $n) => $n->toPhp(), $this->body));

        $php = sprintf($tpl['open'], $loopExpr, $keyPart . $valueVar);
        $php .= $body . "\n";
        $php .= $tpl['end'];

        return $php;
    }

    protected function getTemplates(): array
    {
        return [
            'open' => "foreach (%s as %s):\n",
            'end'  => "endforeach;\n"
        ];
    }

    public function toArray(): array
    {
        return [
            'type'  => TAG::T_FOREACH,
            'loop'  => $this->loop,
            'key'   => $this->key,
            'value' => $this->value,
            'body'  => array_map(fn(Node $n) => $n->toArray(), $this->body),
        ];
    }

    public function getType(): string
    {
        return TAG::T_FOREACH;
    }
}
