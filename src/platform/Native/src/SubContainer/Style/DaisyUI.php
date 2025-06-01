<?php

namespace NativePlatform\SubContainer\Style;

use NativePlatform\SubContainer\Style\UiInterface;
use PlatformBridge\BridgeConfig;
use NativePlatform\SubContainer\Style\DaisyUIKit\ActionsKit;
use NativePlatform\SubContainer\Style\DaisyUIKit\FieldsKit;
use NativePlatform\SubContainer\Style\DaisyUIKit\DataDisplayKit;

class DaisyUI implements UiInterface
{
    protected BridgeConfig $config;
    protected ActionsKit $actionsKit;
    protected FieldsKit $fieldsKit;
    protected DataDisplayKit $dataDisplayKit;

    public function setConfig(BridgeConfig $config)
    {
        $this->config = $config;
    }

    public function render(string $kit, string $component, array $attributes = []): string
    {
        $this->actionsKit = new ActionsKit($kit, $component, $attributes);
        $this->fieldsKit = new FieldsKit($kit, $component, $attributes);
        $this->dataDisplayKit = new DataDisplayKit($kit, $component, $attributes);

        return match ($kit)
        {
            'actions' => $this->actionsKit->render(),
            'fields' => $this->fieldsKit->render(),
            'dataDisplay' => $this->dataDisplayKit->render(),
            default => "<!-- Unknown UI kit: {$kit} -->",
        };
    }

    public function getStyles(): array
    {
        return [
            $this->config->asset('vendor/daisyui/daisyui.css'),
            $this->config->asset('vendor/daisyui/daisyui-themes.css')
        ];
    }

    public function getScripts(): array
    {
        return [
            $this->config->asset('vendor/daisyui/tailwind.js')
        ];
    }
}
