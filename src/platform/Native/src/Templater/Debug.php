<?php

namespace NativePlatform\Templater;

use NativePlatform\Templater\TemplateRegistry;

class Debug
{
    public static function renderDebugInfo(TemplateRegistry $registry, array $compiledData, array $tokens, array $ast): string
    {
        $blocks = array_map(fn($b) => $b->getName(), $compiledData['blocks'] ?? []);
        $extends = $compiledData['extends']?->getTemplate() ?? 'N/A';

        $info = [
            'Template' => $registry->getTemplateName(),
            'Template Path' => $registry->getTemplatePath(),
            'Class Name' => $registry->getClassName(),
            'Cache Path' => $registry->getClassPath(),
            'Extends' => $extends,
            'Blocks' => implode(', ', $blocks),
            'Cache Key' => $registry->getSourceKey(),
        ];

        $html = "<div style=\"background:#222;color:#fff;padding:1em;font-family:monospace;font-size:14px;\">";
        $html .= "<h3 style=\"margin-top:0;color:#0f0;\">Template Debug Info</h3><ul>";

        foreach ($info as $key => $val)
        {
            $html .= "<li><strong>{$key}:</strong> {$val}</li>";
        }

        $html .= "<h4 style='color:#0ff;'>Tokens</h4><pre style='white-space:pre-wrap;color:#ccc;'>" .
            htmlspecialchars(var_export($tokens, true)) . "</pre>";

        $html .= "<h4 style='color:#0ff;'>AST Dump</h4><pre style='white-space:pre-wrap;color:#ccc;'>" .
            htmlspecialchars(var_export($ast, true)) . "</pre>";

        $html .= "</ul></div>";

        $html .= "</ul></div>";
        return $html;
    }
}
