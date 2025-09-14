<?php

namespace NativePlatform\SubContainer\Style;

use NativePlatform\SubContainer\HtmlElement;

abstract class AbstractKit
{
    protected string $kit;
    protected string $component;
    protected array $attributes;

    public function __construct(string $kit, string $component, array $attributes = [])
    {
        $this->kit = $kit;
        $this->component = $component;
        $this->attributes = $attributes;
    }

    protected function htmlElement(string $tag, array $attributes = [], bool $selfClosing = false): HtmlElement
    {
        return new HtmlElement($tag, $attributes, $selfClosing);
    }

    public function render()
    {
        if (!method_exists($this, $this->component))
        {
            throw new \Exception("{$this->component} is not callable on {$this->kit}");
        }

        return call_user_func_array([$this, $this->component], []);
    }
}
