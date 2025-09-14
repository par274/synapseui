<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\Lexer;
use NativePlatform\Templater\Parser;
use NativePlatform\Templater\Engine;
use NativePlatform\Templater\TemplateRegistry;

class Linter
{
    protected Engine $engine;
    protected TemplateRegistry $registry;

    public function __construct(string $templateName, Engine $engine)
    {
        $this->engine = $engine;
        $this->registry = new TemplateRegistry($templateName, $this->engine->cacheDir, $this->engine->templateDir);
    }

    public function lint(): array
    {
        $errors = [];

        $source = $this->registry->get();
        $lexer = new Lexer($source, $this->engine->getPrefix());

        try
        {
            $tokens = $lexer->tokenize();
        }
        catch (\Exception $e)
        {
            $errors[] = 'Lexer error: ' . $e->getMessage();
            return $errors;
        }

        try
        {
            $parser = new Parser($this->engine, $tokens, $this->engine->getPrefix());
            $parser->parse();
        }
        catch (\Exception $e)
        {
            $errors[] = 'Parser error: ' . $e->getMessage();
        }

        return $errors;
    }
}
