<?php

namespace NativePlatform\SubContainer;

class HtmlElement
{
    protected string $tag;
    protected array $attributes = [];
    protected string $text = '';
    protected bool $selfClosing;
    protected array $children = [];
    protected ?HtmlElement $parent = null;

    public function __construct(string $tag = 'div', array $attributes = [], bool $selfClosing = false)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->selfClosing = $selfClosing;
    }

    public function add(string $tag = 'div', array $attributes = [], bool $selfClosing = false): HtmlElement
    {
        $child = new HtmlElement($tag, $attributes, $selfClosing);
        $child->parent = $this;
        $this->children[] = $child;
        return $child;
    }

    public function setText(string $text): static
    {
        $this->text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $this;
    }

    public function setHtml(string $html): static
    {
        $this->text = $html;
        return $this;
    }

    public function setAttribute(string $key, string $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function addChild(HtmlElement $child): HtmlElement
    {
        $child->parent = $this;
        $this->children[] = $child;
        return $child;
    }

    public function render(): string
    {
        $html = "<{$this->tag}";

        foreach ($this->attributes as $key => $value)
        {
            $value = $value ?? '';
            $html .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\"";
        }

        if ($this->selfClosing && empty($this->children) && $this->text === '')
        {
            return $html . ' />';
        }

        $html .= '>';

        if (!empty($this->text))
        {
            $html .= $this->text;
        }

        foreach ($this->children as $child)
        {
            $html .= $child->render();
        }

        if (!in_array($this->tag, ['input', 'img', 'br', 'hr', 'meta', 'link']))
        {
            $html .= "</{$this->tag}>";
        }

        return $html;
    }
}
