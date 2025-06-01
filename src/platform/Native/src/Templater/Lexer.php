<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\Exception\TemplateSyntaxException;

/**
 * Tokenizes a .tpl file into tag/variable/text chunks, with line/column tracking.
 */
class Lexer
{
    protected string $source;
    protected int $pos = 0;
    protected int $length = 0;
    protected string $prefix;
    protected int $line = 1;
    protected int $column = 1;

    public function __construct(string $source, string $prefix = 'sx')
    {
        $this->source = $source;
        $this->length = strlen($source);
        $this->prefix = $prefix;
    }

    public function tokenize(): array
    {
        $tokens = [];

        while ($this->pos < $this->length)
        {
            $char = $this->source[$this->pos];
            $line = $this->line;
            $column = $this->column;

            if ($char === '<' && $this->peekPrefixTag())
            {
                $tagString = $this->readTag();
                if (str_starts_with($tagString, '/'))
                {
                    $tokens[] = ['type' => 'endtag', 'value' => ltrim($tagString, '/'), 'line' => $line, 'column' => $column];
                }
                else
                {
                    $tokens[] = ['type' => 'tag', 'value' => $tagString, 'line' => $line, 'column' => $column];
                }
            }
            elseif ($char === '{' && $this->peekVariable())
            {
                $tokens[] = ['type' => 'var', 'value' => $this->readVariable(), 'line' => $line, 'column' => $column];
            }
            else
            {
                $text = $this->readUntilSpecial();
                $tokens[] = ['type' => 'text', 'value' => $text, 'line' => $line, 'column' => $column];
            }
        }

        return $tokens;
    }

    protected function advance(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++)
        {
            if ($this->pos >= $this->length)
            {
                return;
            }
            $char = $this->source[$this->pos++];
            if ($char === "\n")
            {
                $this->line++;
                $this->column = 1;
            }
            else
            {
                $this->column++;
            }
        }
    }

    protected function peekPrefixTag(): bool
    {
        $next = substr($this->source, $this->pos + 1, strlen($this->prefix) + 2);
        return str_starts_with($next, $this->prefix . ':') || str_starts_with($next, '/' . $this->prefix . ':');
    }

    protected function peekVariable(): bool
    {
        return isset($this->source[$this->pos + 1]) && $this->source[$this->pos + 1] === '$';
    }

    protected function readTag(): string
    {
        $this->advance(); // skip '<'
        $start = $this->pos;
        $inQuote = false;
        $tagClosed = false;

        while ($this->pos < $this->length)
        {
            $char = $this->source[$this->pos];

            if ($char === '"')
            {
                $inQuote = !$inQuote;
            }
            elseif ($char === '>' && !$inQuote)
            {
                $tagClosed = true;
                break;
            }

            $this->advance();
        }

        if (!$tagClosed)
        {
            throw new TemplateSyntaxException("Unclosed tag: missing '>'", $this->line, $this->column);
        }

        $tag = substr($this->source, $start, $this->pos - $start);
        $this->advance(); // skip '>'

        return trim($tag);
    }

    protected function readVariable(): array
    {
        $startLine = $this->line;
        $startCol = $this->column;

        $this->advance(); // skip '{'

        $start = $this->pos;
        $inParen = false;
        $parenDepth = 0;
        $hasCall = false;

        while ($this->pos < $this->length)
        {
            $char = $this->source[$this->pos];

            if ($char === '(')
            {
                $hasCall = true;
                $inParen = true;
                $parenDepth++;
            }
            elseif ($char === ')')
            {
                $parenDepth--;
                if ($parenDepth === 0)
                {
                    $inParen = false;
                }
            }
            elseif ($char === '}' && !$inParen)
            {
                break;
            }

            $this->advance();
        }

        if ($this->pos >= $this->length || $this->source[$this->pos] !== '}')
        {
            throw new TemplateSyntaxException("Unclosed variable expression (missing '}')", $startLine, $startCol);
        }

        $raw = substr($this->source, $start, $this->pos - $start);
        $this->advance(); // skip '}'

        if ($hasCall)
        {
            if (!preg_match('/^([a-zA-Z0-9_\$]+(?:(?:->|\.)[a-zA-Z0-9_]+)*)\((.*?)\)(?:\|(\w+))?$/', $raw, $m))
            {
                throw new TemplateSyntaxException("Invalid function call syntax in variable", $startLine, $startCol);
            }

            $varName = trim($m[1]);
            $argList = trim($m[2]);
            $filter  = isset($m[3]) ? trim($m[3]) : null;

            $args = [];

            foreach (preg_split('/,(?![^()]*\))/', $argList) as $arg)
            {
                $arg = trim($arg);
                if ($arg !== '')
                {
                    $args[] = $arg;
                }
            }

            return [
                'type'   => 'var',
                'value'  => $varName,
                'call'   => true,
                'args'   => $args,
                'filter' => $filter,
                'line'   => $startLine,
                'column' => $startCol
            ];
        }

        return [
            'type' => 'var',
            'value' => trim($raw),
            'line' => $startLine,
            'column' => $startCol
        ];
    }

    protected function readUntilSpecial(): string
    {
        $start = $this->pos;

        while ($this->pos < $this->length)
        {
            if (
                ($this->source[$this->pos] === '<' && $this->peekPrefixTag()) ||
                ($this->source[$this->pos] === '{' && $this->peekVariable())
            )
            {
                break;
            }
            $this->advance();
        }

        return substr($this->source, $start, $this->pos - $start);
    }
}
