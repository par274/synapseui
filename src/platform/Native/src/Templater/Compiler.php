<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\TemplateRegistry;
use NativePlatform\Templater\Node\BlockNode;
use NativePlatform\Templater\Node\MacroNode;

class Compiler
{
    protected string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
    }

    /**
     * @param TemplateRegistry $registry
     * @param Node[] $ast
     * @return string
     */
    public function compile(TemplateRegistry $registry, array $ast): string
    {
        $php  = "<?php\n\n";
        $php .= "namespace Internal\\Templater\\Compiled;\n\n";
        $php .= "use NativePlatform\\Templater\\BaseTemplate;\n\n";
        $php .= "// Template {$registry->getTemplateName()}\n";
        $php .= "class {$registry->getClassName()} extends BaseTemplate\n{\n";
        $php .= "    public string \$cache = \"{$registry->getSourceKey()}\";\n\n";

        // Macro functions (only once)
        $macroNodes = $this->collectMacros($ast);
        foreach ($macroNodes as $macro)
        {
            $php .= rtrim($macro->toPhp()) . "\n\n";
        }

        // Block functions (only once)
        foreach ($ast as $node)
        {
            if ($node instanceof BlockNode)
            {
                $php .= rtrim($node->toPhp()) . "\n\n";
            }
        }

        $php .= "    protected function renderTemplate(): void\n    {\n";

        foreach ($ast as $node)
        {
            if ($node instanceof BlockNode)
            {
                $name   = $node->getName();
                $method = 'renderBlock_' . md5($name);
                $php .= "\$this->renderBlock('{$name}', function() {\n";
                $php .= "\$this->{$method}();\n";
                $php .= "});\n";
            }
            elseif (!($node instanceof MacroNode))
            {
                $code = rtrim($node->toPhp());
                $php .= "{$code}\n";
            }
        }

        $php .= "}\n";
        $php .= "}\n";

        return $php;
    }

    protected function collectMacros(array $nodes): array
    {
        $macros = [];

        foreach ($nodes as $node)
        {
            if ($node instanceof MacroNode)
            {
                $macros[] = $node;
            }

            // If the node has children (like BlockNode), collect from there recursively
            if (method_exists($node, 'getBody'))
            {
                $macros = array_merge($macros, $this->collectMacros($node->getBody()));
            }
            elseif (method_exists($node, 'getChildren'))
            {
                $macros = array_merge($macros, $this->collectMacros($node->getChildren()));
            }
        }

        return $macros;
    }
}
