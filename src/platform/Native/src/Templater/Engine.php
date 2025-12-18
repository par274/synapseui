<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\Exception\TemplateSyntaxException;
use NativePlatform\Templater\Ast\Flattener;
use NativePlatform\Templater\Compiler;
use NativePlatform\Templater\Lexer;
use NativePlatform\Templater\Parser;
use NativePlatform\Templater\FilterRegistry;
use NativePlatform\SubContainer\Style\UiInterface;

class Engine
{
    protected string $prefix = 'sx';

    public string $templateDir;
    public string $cacheDir;
    public array $blockOverrides = [];
    public FilterRegistry $filters;
    protected bool $strictMode = false;
    protected bool $debug = false;
    protected array $debugTokens = [];
    protected array $debugAst = [];
    protected UiInterface $ui;

    protected array $globalContext = [];

    public function __construct(string $templateDir, string $cacheDir)
    {
        $this->filters = new FilterRegistry();
        $this->templateDir = rtrim($templateDir, '/');
        $this->cacheDir = rtrim($cacheDir, '/');
    }

    public function renderFromFile(string $templateName, array $context = []): string
    {
        $file = $this->load($templateName);
        $registry = new TemplateRegistry($file['templateName'], $this->cacheDir, $file['templateDir']);

        return $this->render($registry, $context);
    }

    public function renderFromString(string $templateName, string $source, array $context = []): string
    {
        $registry = new TemplateRegistry($templateName, $this->cacheDir, $this->templateDir, $source);
        $registry->setStringSource($source);

        return $this->render($registry, $context);
    }

    protected function render(TemplateRegistry $registry, array $context = []): string
    {
        $context = [
            ...$context,
            'app' => [
                ...($this->globalContext['app'] ?? []),
                ...($context['app'] ?? []),
            ],
        ];

        if (!$registry->has())
        {
            $compiledData = $this->compileTemplate($registry);

            if (!class_exists($registry->getClass()))
            {
                throw new \RuntimeException("Compiled template class not autoloadable: {$registry->getClassName()}");
            }

            $template = $registry->create();
            $template->setup($registry->getTemplateName(), $registry->getClassPath(), $this->ui, $context);
            $registry->setCacheKey($template->getCacheKey());
        }
        else
        {
            $template = $registry->create();
            $template->setup($registry->getTemplatePath(), $registry->getClassPath(), $this->ui, $context);
            $registry->setCacheKey($template->getCacheKey());

            $compiledData = $this->compileTemplate($registry);
        }

        if (!empty($this->blockOverrides))
        {
            $template->blocks = $this->blockOverrides;
        }

        $extends = $compiledData['extends'] ?? null;
        if ($extends !== null)
        {
            foreach ($compiledData['blocks'] as $block)
            {
                $method = 'getBlockCallback_' . $block->getName();
                $template->blocks[$block->getName()] = $template->$method();
            }

            $this->blockOverrides = $template->blocks;
            return $this->renderFromFile($extends->getTemplate(), $context);
        }

        $output = $template->render();

        if ($this->debug)
        {
            $output .= Debug::renderDebugInfo($registry, $compiledData, $this->getDebugTokens(), $this->getDebugAst());
        }

        return $output;
    }

    public function compileTemplate(TemplateRegistry $registry): array
    {
        $source = $registry->get();
        $lexer = new Lexer($source, $this->prefix);
        $tokens = $lexer->tokenize();
        if ($this->debug)
        {
            $this->debugTokens = $tokens;
        }

        $parser = new Parser($this, $tokens, $this->prefix);
        try
        {
            $ast = $parser->parse();
        }
        catch (TemplateSyntaxException $e)
        {
            echo $e->getMessage();
            exit(1);
        }

        $compiler = new Compiler($this->cacheDir);
        $compiled = $compiler->compile($registry, $ast);
        $registry->register($compiled);

        $flattener = new Flattener();
        $flattener->collect($ast, $registry->getTemplateName());
        if ($this->debug)
        {
            $this->debugAst = array_map(fn($node) => $node->toArray(), $ast);
        }

        return [
            'ast' => $flattener->get(),
            'blocks' => $parser->getBlocks(),
            'extends' => $parser->getExtends()
        ];
    }

    protected function load(string $templateName): array
    {
        if (str_starts_with($templateName, '@'))
        {
            // @src/templates/test.tpl → templateDir: src/templates, templateName: test.tpl
            $cleanPath = ltrim($templateName, '@');
            $parts = explode('/', $cleanPath);

            if (count($parts) < 2)
            {
                throw new \InvalidArgumentException("Invalid template path: {$templateName}");
            }

            $file = array_pop($parts); // test.tpl
            $dir  = implode('/', $parts); // src/templates

            $templateDir = rtrim($dir, '/');
            $templateName = $file;

            return [
                'templateDir' => ROOT_DIR . '/' . $templateDir,
                'templateName' => $templateName
            ];
        }

        return [
            'templateDir' => $this->templateDir,
            'templateName' => $templateName
        ];
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function enableStrictMode(): void
    {
        $this->strictMode = true;
    }

    public function isStrict(): bool
    {
        return $this->strictMode;
    }

    public function setGlobal(array $globals = []): void
    {
        if (!in_array('app', $this->globalContext))
        {
            $this->globalContext['app'] = [];
        }

        foreach ($globals as $key => $value)
        {
            $this->globalContext['app'][$key] = $value;
        }
    }

    public function enableDebug(): void
    {
        $this->debug = true;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getDebugTokens(): array
    {
        return $this->debugTokens;
    }

    public function getDebugAst(): array
    {
        return $this->debugAst;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setUI($ui): void
    {
        $this->ui = $ui;
    }
}
