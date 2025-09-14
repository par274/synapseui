<?php

namespace NativePlatform\Templater;

class FilterRegistry
{
    protected array $filters = [];

    public function __construct()
    {
        $this->registerDefaultFilters();
    }

    public function register(string $filterName, $classMethod)
    {
        if ($this->has($filterName))
        {
            throw new \Exception("This filter already registered.");
        }

        $this->filters[$filterName] = ['name' => $filterName, 'callable' => $classMethod];
    }

    public function registerDefaultFilters()
    {
        $this->register('capitalize', '\\NativePlatform\\Templater\\Filter\\FormattingFilter::compileCap');
        $this->register('lower', '\\NativePlatform\\Templater\\Filter\\FormattingFilter::compileLower');
        $this->register('ucwords', '\\NativePlatform\\Templater\\Filter\\FormattingFilter::compileUpperFirst');
        $this->register('escape', '\\NativePlatform\\Templater\\Filter\\FormattingFilter::escape');
        $this->register('raw', '\\NativePlatform\\Templater\\Filter\\RawFilter::compile');
        $this->register('dump', '\\NativePlatform\\Templater\\Filter\\DumpFilter::compile');
        $this->register('sy-dump', '\\NativePlatform\\Templater\\Filter\\DumpFilter::compileAlternative');
    }

    public function get(string $filterName)
    {
        if ($this->has($filterName))
        {
            return $this->filters[$filterName];
        }

        return false;
    }

    public function all(): array
    {
        return $this->filters;
    }

    public function has(string $filterName)
    {
        if (isset($this->filters[$filterName]))
        {
            return true;
        }

        return false;
    }
}
