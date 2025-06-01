<?php

namespace NativePlatform\Templater\Node;

use NativePlatform\Templater\Node;
use NativePlatform\Templater\Tag;
use NativePlatform\Templater\Expr\ExprTransformer;

/**
 * Represents a UI component node such as <sx:ui-button> or <sx:ui-dropdown>.
 *
 * These nodes are rendered through the selected UI class (e.g., DaisyUI, BootstrapUI)
 * and not handled like regular HTML or core template nodes.
 *
 * Syntax examples:
 *
 *     <sx:ui-button label="Click me" type="submit" />
 *     <sx:ui-dropdown label="Menu" items="$items" />
 *     <sx:ui-base styles />
 *
 * The `component` part is resolved from the tag name, e.g., `dropdown` from `<sx:ui-dropdown>`.
 * Attributes can contain:
 *     - Static values: label="Click"
 *     - Dynamic context variables: items="$items", data="$foo.bar"
 *
 * The component and its attributes are passed to the UI rendering system:
 *
 *     $this->renderUiComponent('dropdown', [...]);
 *
 * This allows flexible, component-based rendering for different UI kits.
 */
class UiNode implements Node
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

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toPhp(): string
    {
        $compiledAttributes = [];

        foreach ($this->attributes as $key => $value)
        {
            if (str_starts_with($value, '$'))
            {
                $compiledValue = ExprTransformer::transformVar($value);
            }
            else
            {
                $compiledValue = var_export($value, true);
            }

            $compiledAttributes[] = var_export($key, true) . ' => ' . $compiledValue;
        }

        $compiledArray = '[' . implode(', ', $compiledAttributes) . ']';

        return "\$this->renderUiComponent(" . var_export($this->kit, true) . ", " . var_export($this->component, true) . ", {$compiledArray});";
    }

    public function toArray(): array
    {
        return [
            'type' => TAG::T_UI,
            'component' => $this->component,
            'attributes' => $this->attributes
        ];
    }

    public function getType(): string
    {
        return TAG::T_UI;
    }
}
