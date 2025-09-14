<?php

namespace NativePlatform\Templater\Exception;

class TemplateSyntaxException extends \Exception
{
    protected int $line;
    protected int $column;

    public function __construct(string $message, int $line = 0, int $column = 0)
    {
        parent::__construct("Template error at line {$line}, column {$column}: {$message}");
        $this->line = $line;
        $this->column = $column;
    }

    public function getLineNumber(): int
    {
        return $this->line;
    }

    public function getColumnNumber(): int
    {
        return $this->column;
    }
}
