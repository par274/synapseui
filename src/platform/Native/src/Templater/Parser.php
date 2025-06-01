<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\Exception\TemplateSyntaxException;
use NativePlatform\Templater\SymbolTable;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Node\{
    TextNode,
    VariableNode,
    IfNode,
    ForeachNode,
    BlockNode,
    ExtendsNode,
    SetNode,
    IncludeNode,
    RawNode,
    CommentNode,
    MacroNode,
    CallMacroNode,
    UiNode
};
use NativePlatform\Templater\Tag;

class Parser
{
    protected array $tokens;
    protected int $pos = 0;
    protected string $prefix;
    protected ?ExtendsNode $extends = null;
    protected array $blocks = [];
    protected array $macros = [];
    protected Engine $engine;

    public SymbolTable $symbols;

    private $basePatterns = [
        'global' => [
            'attr' => [
                'template' => '/template\s*=\s*"([^"]+)"/',
                'name' => '/name\s*=\s*"([^"]+)"/'
            ]
        ],
        'set' => [
            'attr' => [
                'var' => '/var\s*=\s*"([^"]+)"',
                'value' => '\s+value\s*=\s*"([^"]+)"/'
            ]
        ],
        'if' => [
            'attr' => [
                'is' => '/is\s*=\s*"([^"]+)"/'
            ]
        ],
        'foreach' => [
            'attr' => [
                'loop' => '/loop\s*=\s*"([^"]+)"(\s+key\s*=\s*"([^"]+)")?\s+value\s*=\s*"([^"]+)"/'
            ]
        ],
        'include' => [
            'attr' => [
                'var' => '/var\s*=\s*"([^"]+)"',
                'template' => '\s+template\s*=\s*"([^"]+)"/'
            ]
        ],
        'macro' => [
            'attr' => [
                'macro' => '/macro\s*=\s*"([^"]+)"/',
                'args' => '/(\w+)\s*=\s*"([^"]+)"/'
            ]
        ],
        'ui' => [
            'base' => ':ui-([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)\s*(.*)$/s',
            'attr' => '/(\w+)\s*=\s*"([^"]+)"/'
        ]
    ];

    public function __construct(Engine $engine, array $tokens, string $prefix = 'sx', ?SymbolTable $symbols = null)
    {
        $this->engine = $engine;
        $this->tokens = $tokens;
        $this->prefix = $prefix;
        $this->symbols = $symbols ?? new SymbolTable();

        Tag::setPrefix($this->prefix);
    }

    public function parse(): array
    {
        return $this->parseUntil([]);
    }

    protected function parseUntil(array $stopTags): array
    {
        $nodes = [];

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];

            if (!isset($token['type'], $token['value']))
            {
                throw new TemplateSyntaxException("Invalid token structure", $token['line'] ?? 0, $token['column'] ?? 0);
            }

            if ($token['type'] === 'endtag' && in_array(trim($token['value']), $stopTags, true))
            {
                break;
            }

            $nodes[] = $this->parseNext();
        }

        return $nodes;
    }

    protected function parseNext(): Node
    {
        $token = $this->tokens[$this->pos];

        if (!isset($token['type'], $token['value']))
        {
            throw new TemplateSyntaxException("Invalid token", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        if ($token['type'] === 'text')
        {
            $this->pos++;
            return new TextNode($token['value']);
        }

        if ($token['type'] === 'var')
        {
            $this->pos++;

            $value = $token['value'];

            if (is_array($value))
            {
                $name = $value['value'] ?? '';
                $filter = $value['filter'] ?? '';
                $call = $value['call'] ?? false;
                $args = $value['args'] ?? [];
            }
            else
            {
                $name = $value;
                $call = false;
                $args = [];
            }

            return new VariableNode($this->engine, $this->symbols, $name, $call, $args, $filter);
        }

        if ($token['type'] === 'tag')
        {
            $tagValue = trim($token['value']);

            if (str_starts_with($tagValue, Tag::get('if')))
            {
                return $this->parseIf($tagValue);
            }

            if (str_starts_with($tagValue, Tag::get('foreach')))
            {
                return $this->parseForeach($tagValue);
            }

            if (str_starts_with($tagValue, Tag::get('block')))
            {
                $block = $this->parseBlock($tagValue);
                $this->blocks[$block->getName()] = $block;
                return $block;
            }

            if (str_starts_with($tagValue, Tag::get('extends')))
            {
                if (!preg_match($this->basePatterns['global']['attr']['template'], $tagValue, $m))
                {
                    throw new TemplateSyntaxException("Missing 'template' attribute in extends tag", $token['line'] ?? 0, $token['column'] ?? 0);
                }
                $extends = new ExtendsNode($m[1]);
                $this->extends = $extends;
                $this->pos++;
                return $extends;
            }

            if (str_starts_with($tagValue, Tag::get('set')))
            {
                if (!preg_match("{$this->basePatterns['set']['attr']['var']}{$this->basePatterns['set']['attr']['value']}", $tagValue, $m))
                {
                    throw new TemplateSyntaxException("Invalid set tag syntax", $token['line'] ?? 0, $token['column'] ?? 0);
                }
                $varName = ltrim($m[1], '$');
                $valueExpr = $m[2];
                $this->symbols->define($varName, 'local');
                $this->pos++;
                return new SetNode('$' . $varName, $valueExpr);
            }

            if (str_starts_with($tagValue, Tag::get('include')))
            {
                if (!preg_match("{$this->basePatterns['include']['attr']['var']}{$this->basePatterns['include']['attr']['template']}", $tagValue, $m))
                {
                    throw new TemplateSyntaxException("Invalid include tag syntax", $token['line'] ?? 0, $token['column'] ?? 0);
                }
                $this->pos++;
                return new IncludeNode($this->engine, $m[1], $m[2]);
            }

            if (str_starts_with($tagValue, Tag::get('comment')))
            {
                $this->pos++;
                $this->skipUntilEndTag('comment');
                return new CommentNode();
            }

            if (str_starts_with($tagValue, Tag::get('raw')))
            {
                return $this->parseRaw();
            }

            if (str_starts_with($tagValue, Tag::get('macro')))
            {
                $macro = $this->parseMacro($tagValue);
                $this->macros[$macro->getName()] = $macro;

                return $macro;
            }

            if (str_starts_with($tagValue, Tag::get('call')))
            {
                return $this->parseCall($tagValue);
            }

            if (str_starts_with($tagValue, $this->prefix . ':ui-'))
            {
                return $this->parseUi($tagValue);
            }

            throw new TemplateSyntaxException("Unknown tag: {$tagValue}", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $this->pos++;
        return new TextNode('');
    }

    protected function parseIf(string $tagValue): IfNode
    {
        $token = $this->tokens[$this->pos];

        if (!preg_match($this->basePatterns['if']['attr']['is'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Missing or invalid 'is' attribute in if tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $node = new IfNode($m[1]);
        $this->pos++;
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'tag')
            {
                $name = trim($token['value']);
                if (str_starts_with($name, Tag::get('elseif')) || str_starts_with($name, Tag::get('else')))
                {
                    break;
                }
            }
            if ($token['type'] === 'endtag' && trim($token['value']) === $this->prefix . Tag::get('if'))
            {
                $foundEnd = true;
                $this->pos++;
                return $node;
            }
            $node->addChild($this->parseNext());
        }

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];

            if ($token['type'] === 'tag' && str_starts_with(trim($token['value']), $this->prefix . ":elseif"))
            {
                preg_match($this->basePatterns['if']['attr']['is'], $token['value'], $m);
                $condition = $m[1] ?? '';
                $this->pos++;

                $body = [];
                while ($this->pos < count($this->tokens))
                {
                    $t = $this->tokens[$this->pos];
                    if ($t['type'] === 'tag')
                    {
                        $n = trim($t['value']);
                        if (str_starts_with($n, Tag::get('elseif')) || str_starts_with($n, Tag::get('else')))
                        {
                            break;
                        }
                    }
                    if ($t['type'] === 'endtag' && trim($t['value']) === Tag::get('if'))
                    {
                        $foundEnd = true;
                        $this->pos++;
                        break 2;
                    }
                    $body[] = $this->parseNext();
                }

                $node->addElseif($condition, $body);
                continue;
            }

            if ($token['type'] === 'tag' && str_starts_with(trim($token['value']), Tag::get('else')))
            {
                $this->pos++;
                $body = [];
                while ($this->pos < count($this->tokens))
                {
                    $t = $this->tokens[$this->pos];
                    if ($t['type'] === 'endtag' && trim($t['value']) === Tag::get('if'))
                    {
                        $foundEnd = true;
                        $this->pos++;
                        break;
                    }
                    $body[] = $this->parseNext();
                }
                $node->setElse($body);
                break;
            }

            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get('if'))
            {
                $foundEnd = true;
                $this->pos++;
                break;
            }

            $this->pos++;
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:if tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        return $node;
    }

    protected function parseBlock(string $tagValue): BlockNode
    {
        $token = $this->tokens[$this->pos];

        if (!preg_match($this->basePatterns['global']['attr']['name'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Missing 'name' attribute in block tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $node = new BlockNode($m[1]);
        $this->pos++;
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get('block'))
            {
                $this->pos++;
                $foundEnd = true;
                break;
            }
            $node->addChild($this->parseNext());
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:block tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        return $node;
    }

    protected function parseForeach(string $tagValue): ForeachNode
    {
        $token = $this->tokens[$this->pos];

        if (!preg_match($this->basePatterns['foreach']['attr']['loop'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Invalid foreach syntax", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $loop = $m[1];
        $key = $m[3] ?? null;
        $value = $m[4];

        $node = new ForeachNode($this->symbols, $loop, $value, $key);
        $this->pos++;
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get('foreach'))
            {
                $this->pos++;
                $foundEnd = true;
                break;
            }
            $node->addChild($this->parseNext());
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:foreach tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        return $node;
    }

    protected function parseRaw(): RawNode
    {
        $node = new RawNode();
        $this->pos++;
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get('raw'))
            {
                $this->pos++;
                $foundEnd = true;
                break;
            }
            $node->addChild($this->parseNext());
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:raw tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        return $node;
    }

    protected function parseMacro(string $tagValue): MacroNode
    {
        $token = $this->tokens[$this->pos];

        if (!preg_match($this->basePatterns['global']['attr']['name'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Missing 'name' attribute in macro tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $name = $m[1];

        // Extract all arguments excluding 'name'
        preg_match_all($this->basePatterns['macro']['attr']['args'], $tagValue, $matches, PREG_SET_ORDER);
        $args = [];

        foreach ($matches as $match)
        {
            if ($match[1] !== 'name')
            {
                $args[] = [
                    'name' => $match[1],
                    'var'  => $match[2]
                ];
            }
        }

        $this->pos++;
        $body = [];
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get('macro'))
            {
                $this->pos++;
                $foundEnd = true;
                break;
            }
            $body[] = $this->parseNext();
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:macro tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $macro = new MacroNode($name, $args, $body);
        $this->macros[$name] = $macro;

        return $macro;
    }

    protected function parseCall(string $tagValue): CallMacroNode
    {
        if (!preg_match($this->basePatterns['macro']['attr']['macro'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Missing 'macro' attribute in call tag", $this->tokens[$this->pos]['line'] ?? 0);
        }

        $name = $m[1];

        preg_match_all($this->basePatterns['macro']['attr']['args'], $tagValue, $matches, PREG_SET_ORDER);
        $args = [];

        foreach ($matches as $match)
        {
            if ($match[1] !== 'macro')
            {
                $args[$match[1]] = $match[2];
            }
        }

        $this->pos++;
        return new CallMacroNode($name, $args);
    }

    protected function skipUntilEndTag(string $name): void
    {
        $foundEnd = false;

        while ($this->pos < count($this->tokens))
        {
            $token = $this->tokens[$this->pos];
            if ($token['type'] === 'endtag' && trim($token['value']) === Tag::get($name))
            {
                $this->pos++;
                $foundEnd = true;
                break;
            }
            $this->pos++;
        }

        if (!$foundEnd)
        {
            throw new TemplateSyntaxException("Unclosed {$this->prefix}:{$name} tag", $token['line'] ?? 0, $token['column'] ?? 0);
        }
    }

    protected function parseUi(string $tagValue): UiNode
    {
        $token = $this->tokens[$this->pos];

        // Match "ui-{componentName}"
        if (!preg_match('/^' . preg_quote($this->prefix, '/') . $this->basePatterns['ui']['base'], $tagValue, $m))
        {
            throw new TemplateSyntaxException("Invalid UI tag syntax", $token['line'] ?? 0, $token['column'] ?? 0);
        }

        $kit = $m[1]; // actions, fields, layout eg.
        $component = $m[2]; // button, dropdown, base, eg.
        $attrPart = trim($m[3]); // name="btn1"

        // Get attrs
        $attributes = [];
        if (preg_match_all($this->basePatterns['ui']['attr'], $attrPart, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                $attributes[$match[1]] = $match[2];
            }
        }

        $this->pos++;
        return new UiNode($kit, $component, $attributes);
    }

    public function getExtends(): ?ExtendsNode
    {
        return $this->extends;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getMacros(): array
    {
        return $this->macros;
    }
}
