<?php

namespace NativePlatform\SubContainer\Style\DaisyUIKit;

use NativePlatform\SubContainer\Style\AbstractKit;

class ActionsKit extends AbstractKit
{
    public function dropdown(): string
    {
        $label = $this->attributes['label'] ?? 'Dropdown';
        $items = $this->attributes['items'] ?? [];

        $html = $this->htmlElement('div', ['class' => 'dropdown']);

        $html->add('div', [
            'tabindex' => '0',
            'role' => 'button',
            'class' => 'btn m-1'
        ])->setText($label);

        $ul = $html->add('ul', [
            'tabindex' => '0',
            'class' => 'dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm'
        ]);

        foreach ($items as $item)
        {
            $ul->add('li')->add('a', [
                'href' => $item['url'] ?? '#'
            ])->setText($item['label'] ?? 'Seçenek');
        }

        return $html->render();
    }

    public function button(): string
    {
        $text = $this->attributes['text'] ?? 'Button';
        $type = $this->attributes['type'] ?? 'button';
        $variant = $this->attributes['variant'] ?? 'neutral'; // btn-neutral default
        $modalTarget = $this->attributes['modal'] ?? null;

        $extraClass = $this->attributes['class'] ?? '';
        $extra = $this->attributes['extra'] ?? [];
        $extra = is_array($extra) ? $extra : [];

        // normalize keyless extras
        $extra = array_filter($extra, fn($v) => !is_null($v));
        $normalized = [];
        foreach ($extra as $k => $v)
        {
            $normalized[is_int($k) ? $v : $k] = is_int($k) ? $v : $v;
        }

        $classes = trim("btn btn-{$variant} {$extraClass}");

        $attributes = [
            'type' => $type,
            'class' => $classes,
            ...$normalized
        ];

        if ($modalTarget)
        {
            $attributes['onclick'] = "document.getElementById('{$modalTarget}').showModal()";
        }

        $html = $this->htmlElement('button', $attributes);

        $html->setText($text);

        return $html->render();
    }

    public function modal(): string
    {
        $id = $this->attributes['id'] ?? 'modal_' . uniqid();
        $title = $this->attributes['title'] ?? 'Modal Title';
        $content = $this->attributes['content'] ?? 'Modal content goes here...';
        $sizeClass = $this->attributes['size'] ?? 'modal-bottom sm:modal-middle';

        $dialog = $this->htmlElement('dialog', [
            'id' => $id,
            'class' => "modal {$sizeClass}"
        ]);

        $modalBox = $dialog->add('div', ['class' => 'modal-box']);

        $modalBox->add('form', ['method' => 'dialog'])
            ->add('button', [
                'class' => 'btn btn-sm btn-circle btn-ghost absolute right-2 top-2'
            ])->setText('✕');

        $modalBox->add('h3', ['class' => 'text-lg font-bold'])->setText($title);
        $modalBox->add('p', ['class' => 'py-4'])->setText($content);

        $dialog->add('form', ['method' => 'dialog', 'class' => 'modal-backdrop'])
            ->add('button')->setText('close');

        return $dialog->render();
    }
}
