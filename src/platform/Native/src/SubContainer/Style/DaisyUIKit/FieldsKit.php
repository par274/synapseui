<?php

namespace NativePlatform\SubContainer\Style\DaisyUIKit;

use NativePlatform\SubContainer\Style\AbstractKit;
use NativePlatform\SubContainer\HtmlElement;

class FieldsKit extends AbstractKit
{
    public function form(): string
    {
        $method = $this->attributes['method'] ?? 'post';
        $inputs = $this->attributes['inputs'] ?? [];
        $grid = isset($this->attributes['grid']);

        $hasFileInput = array_filter($inputs, fn($i) => ($i['type'] ?? '') === 'file');
        $enctype = $hasFileInput ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        $formClass = $grid
            ? 'grid grid-cols-1 md:grid-cols-2 gap-4'
            : 'space-y-4';

        $html = $this->htmlElement('form', [
            'method' => $method,
            'enctype' => $enctype,
            'class' => $formClass
        ]);

        foreach ($inputs as $input)
        {
            $type = $input['type'] ?? 'input';
            $span = $input['column_span'] ?? 1;
            $wrapper = $html->add('div', ['class' => "col-span-{$span}"]);

            $field = match ($type)
            {
                'input' => $this->input($input),
                'textarea' => $this->textarea($input),
                'checkbox' => $this->checkbox($input),
                'radio' => $this->radio($input),
                'select' => $this->select($input),
                'button' => $this->button($input),
                'file' => $this->fileInput($input),
                default => null
            };

            if ($field instanceof HtmlElement)
            {
                $wrapper->addChild($field);
            }
        }

        return $html->render();
    }

    public function input(array $attrs): HtmlElement
    {
        $extra = $attrs['extra'] ?? [];
        $extra = is_array($extra) ? $extra : [];

        $extra = array_filter($extra, fn($v) => !is_null($v));

        $html = $this->htmlElement('fieldset', ['class' => 'fieldset']);
        $html->add('legend', ['class' => 'fieldset-legend'])
            ->setText($attrs['label'] ?? '');

        $html->add('input', [
            'type' => $attrs['input_type'] ?? 'text',
            'name' => $attrs['name'] ?? '',
            'value' => $attrs['value'] ?? '',
            'placeholder' => $attrs['placeholder'] ?? '',
            'class' => $attrs['class'] ?? 'input input-bordered w-full',
            ...$extra
        ]);

        $html->add('p', ['class' => 'label text-sm text-gray-500'])
            ->setText($attrs['note'] ?? '');

        return $html;
    }

    public function textarea(array $attrs): HtmlElement
    {
        $html = $this->htmlElement('fieldset', ['class' => 'fieldset']);
        $html->add('legend', ['class' => 'fieldset-legend'])
            ->setText($attrs['label'] ?? '');

        $html->add('textarea', [
            'name' => $attrs['name'] ?? '',
            'class' => 'textarea textarea-bordered w-full'
        ])->setText($attrs['value'] ?? '');

        $html->add('p', ['class' => 'label text-sm text-gray-500'])
            ->setText($attrs['note'] ?? '');

        return $html;
    }

    public function checkbox(array $attrs): HtmlElement
    {
        $html = $this->htmlElement('fieldset', ['class' => 'fieldset']);
        $html->add('legend', ['class' => 'fieldset-legend'])
            ->setText($attrs['label'] ?? '');

        $label = $html->add('label', ['class' => 'label cursor-pointer']);
        $label->add('input', [
            'type' => 'checkbox',
            'name' => $attrs['name'] ?? '',
            'class' => 'checkbox',
            'checked' => isset($attrs['checked']) ? 'checked' : null
        ]);
        $label->add('span', ['class' => 'ml-2'])
            ->setText($attrs['note'] ?? '');

        return $html;
    }

    public function radio(array $attrs): HtmlElement
    {
        $html = $this->htmlElement('div', ['class' => 'form-control']);

        $label = $html->add('label', ['class' => 'label cursor-pointer']);
        $label->add('span', ['class' => 'label-text'])
            ->setText($attrs['label'] ?? '');
        $label->add('input', [
            'type' => 'radio',
            'name' => $attrs['name'] ?? '',
            'value' => $attrs['value'] ?? '',
            'class' => 'radio',
            'checked' => isset($attrs['checked']) ? 'checked' : null
        ]);

        return $html;
    }

    public function select(array $attrs): HtmlElement
    {
        $html = $this->htmlElement('div', ['class' => 'form-control']);

        $html->add('label', ['class' => 'label'])
            ->add('span', ['class' => 'label-text'])
            ->setText($attrs['label'] ?? '');

        $select = $html->add('select', [
            'name' => $attrs['name'] ?? '',
            'class' => 'select select-bordered w-full'
        ]);

        foreach (($attrs['options'] ?? []) as $val => $text)
        {
            $select->add('option', ['value' => $val])
                ->setText($text);
        }

        return $html;
    }

    public function button(array $attrs): HtmlElement
    {
        $html = $this->htmlElement('button', [
            'type' => $attrs['type'] ?? 'submit',
            'class' => $attrs['class'] ?? 'btn btn-primary'
        ]);

        $html->setText($attrs['text'] ?? 'Submit');

        return $html;
    }

    public function fileInput(array $attrs): HtmlElement
    {
        $extra = $attrs['extra'] ?? [];
        $extra = is_array($extra) ? $extra : [];

        $html = $this->htmlElement('fieldset', ['class' => 'fieldset']);

        $html->add('legend', ['class' => 'fieldset-legend'])
            ->setText($attrs['label'] ?? 'Select file');

        $html->add('input', [
            'type' => 'file',
            'name' => $attrs['name'] ?? '',
            'class' => $attrs['class'] ?? 'file-input file-input-bordered w-full',
            ...$extra
        ]);

        $html->add('p', ['class' => 'label text-sm text-gray-500'])
            ->setText($attrs['note'] ?? '');

        return $html;
    }
}
