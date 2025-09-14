<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Node;
use NativePlatform\Templater\Engine;

/**
 * Represents a template inclusion via <sx:include>.
 *
 * Syntax:
 *     <sx:include var="footer" template="footer.tpl" />
 *     {$footer}
 *
 * Includes and renders another compiled template within this one.
 */
class IncludeNode implements Node
{
    protected string $template;
    protected string $var;
    protected Engine $engine;

    public function __construct(Engine $engine, string $var, string $template)
    {
        $this->engine = $engine;
        $this->var = $var;
        $this->template = $template;
    }

    public function toPhp(): string
    {
        $tpl = $this->getTemplates();

        return sprintf(
            $tpl['include'],
            $this->var,
            $this->engine->templateDir,
            $this->engine->cacheDir,
            $this->template
        );
    }

    protected function getTemplates(): array
    {
        return [
            'include' => "\$this->context['%s'] = \$this->incl(['template_dir' => '%s', 'cache_dir' => '%s'], '%s', \$this->context);"
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_INCLUDE,
            'template' => $this->template
        ];
    }

    public function getType(): string
    {
        return TAG::T_INCLUDE;
    }
}
