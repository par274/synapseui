<?php

namespace NativePlatform\Templater;

use NativePlatform\SubContainer\Style\UiInterface;

/**
 * Base class for all compiled template classes.
 */
abstract class BaseTemplate
{
    protected string $cache;

    public array $context = [];
    public array $blocks = [];
    public array $templateBaseContext = [];

    protected string $name;
    protected string $templatePath;
    protected UiInterface $ui;

    public function setup(string $templateName, string $templatePath, UiInterface $ui, array $context = [])
    {
        $this->name = $templateName;
        $this->templatePath = $templatePath;
        $this->context = array_merge($context, [
            'this' => [
                'name' => $templateName,
                'path' => $templatePath
            ]
        ]);
        $this->ui = $ui;
    }

    public function render(): string
    {
        ob_start();
        $this->renderTemplate();
        return ob_get_clean();
    }

    abstract protected function renderTemplate(): void;

    protected function echoText(mixed $value): void
    {
        echo htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function echoRaw(mixed $value): void
    {
        echo (string)$value;
    }

    protected function incl(array $templateConfig, string $templateName, array $context = [])
    {
        $templater = new Engine($templateConfig['template_dir'], $templateConfig['cache_dir']);
        $templater->setUI($this->ui);
        return $templater->renderFromFile($templateName, $context);
    }

    public function renderBlock(string $name, \Closure $default): void
    {
        if (isset($this->blocks[$name]) && is_callable($this->blocks[$name]))
        {
            call_user_func($this->blocks[$name]);
        }
        else
        {
            $default();
        }
    }

    protected function renderUiComponent(string $kit, string $component, array $attributes = []): void
    {
        echo $this->ui->render($kit, $component, $attributes);
    }

    public function getCacheKey(): string
    {
        return $this->cache;
    }

    protected function contextGetStrict($base, array $parts): mixed
    {
        foreach ($parts as $part)
        {
            if (is_array($base) && array_key_exists($part, $base))
            {
                $base = $base[$part];
                continue;
            }

            if (is_object($base))
            {
                if (isset($base->$part) || property_exists($base, $part))
                {
                    $base = $base->$part;
                    continue;
                }

                if ($base instanceof \ArrayAccess && isset($base[$part]))
                {
                    $base = $base[$part];
                    continue;
                }

                if (is_object($base) && method_exists($base, $part))
                {
                    return [$base, $part];
                }
            }

            return '';
        }

        return $base;
    }
}
