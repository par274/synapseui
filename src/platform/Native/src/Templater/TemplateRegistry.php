<?php

namespace NativePlatform\Templater;

class TemplateRegistry
{
    protected string $templateName;
    protected array $map = [];
    protected string|bool $source = false;
    protected $mapTemplate = [
        'className' => 'CompiledTemplate_%s',
        'path' => '%s/%s',
        'class' => '\\Internal\\Templater\\Compiled\\CompiledTemplate_%s',
        'classPath' => '%s/%s.php'
    ];

    private $templateCacheKey;

    public function __construct(string $templateName, string $cacheDir, string $templateDir)
    {
        $this->templateName = $templateName;
        $className = sprintf($this->mapTemplate['className'], md5($templateName));

        $this->map[$templateName] = [
            'name' => $templateName,
            'path' => sprintf($this->mapTemplate['path'], $templateDir, $templateName),
            'class' => sprintf($this->mapTemplate['class'], md5($templateName)),
            'className' => $className,
            'classPath' => sprintf($this->mapTemplate['classPath'], $cacheDir, $className)
        ];
    }

    public function register(string $compiled)
    {
        if (!$this->source)
        {
            if (!file_exists($this->getClassPath()) || filemtime($this->getClassPath()) < filemtime($this->getTemplatePath()))
            {
                file_put_contents($this->getClassPath(), $compiled);

                if (function_exists('opcache_compile_file'))
                {
                    opcache_invalidate($this->getClassPath(), true);
                }
            }
        }
        else
        {
            if ($this->getSourceKey() !== $this->templateCacheKey)
            {
                file_put_contents($this->getClassPath(), $compiled);

                if (function_exists('opcache_compile_file'))
                {
                    opcache_invalidate($this->getClassPath(), true);
                }
            }
        }
    }

    public function has(): bool
    {
        if (!file_exists($this->getClassPath()))
        {
            return false;
        }

        if (!class_exists($this->getClass()))
        {
            return false;
        }

        return true;
    }

    public function get(): string
    {
        if (!$this->source)
        {
            if (!file_exists($this->getTemplatePath()))
            {
                throw new \RuntimeException("Template not found: {$this->getTemplatePath()}");
            }

            return file_get_contents($this->getTemplatePath());
        }
        else
        {
            return $this->source;
        }
    }

    public function create(): object
    {
        $class = $this->getClass();
        return new $class();
    }

    public function getTemplateName(): string
    {
        return $this->map[$this->templateName]['name'];
    }

    public function getTemplatePath(): string
    {
        return $this->map[$this->templateName]['path'];
    }

    public function getClass(): string
    {
        return $this->map[$this->templateName]['class'];
    }

    public function getClassName(): string
    {
        return $this->map[$this->templateName]['className'];
    }

    public function getClassPath(): string
    {
        return $this->map[$this->templateName]['classPath'];
    }

    public function getSourceKey(): string
    {
        return md5($this->get());
    }

    public function setStringSource(string $source)
    {
        $this->source = $source;
    }

    public function setCacheKey(string $templateCacheKey)
    {
        $this->templateCacheKey = $templateCacheKey;
    }
}
