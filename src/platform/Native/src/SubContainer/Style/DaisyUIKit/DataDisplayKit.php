<?php

namespace NativePlatform\SubContainer\Style\DaisyUIKit;

use NativePlatform\SubContainer\Style\AbstractKit;

class DataDisplayKit extends AbstractKit
{
    public function table(): string
    {
        $head = $this->attributes['head'] ?? [];
        $body = $this->attributes['body'] ?? [];

        $html = $this->htmlElement('div', [
            'class' => 'overflow-x-auto rounded-box border border-base-content/5 bg-base-100'
        ]);

        $table = $html->add('table', ['class' => 'table']);

        // Table head
        if (!empty($head))
        {
            $thead = $table->add('thead');
            $tr = $thead->add('tr');
            foreach ($head as $th)
            {
                $tr->add('th')->setText($th);
            }
        }

        // Table body
        if (!empty($body))
        {
            $tbody = $table->add('tbody');
            foreach ($body as $row)
            {
                $tr = $tbody->add('tr');
                foreach ($row as $cell)
                {
                    if (is_array($cell))
                    {
                        $th = $tr->add('th');
                        $th->setText($cell['content']);
                    }
                    else
                    {
                        $td = $tr->add('td');
                        $td->setText($cell);
                    }
                }
            }
        }

        return $html->render();
    }

    public function accordion(): string
    {
        $items = $this->attributes['items'] ?? [];

        $html = $this->htmlElement('div', ['class' => 'join join-vertical bg-base-100']);

        foreach ($items as $index => $item)
        {
            $collapse = $html->add('div', [
                'class' => 'collapse collapse-arrow join-item border-base-300 border'
            ]);

            $inputAttr = ['type' => 'radio', 'name' => 'my-accordion-' . count($items)];
            if ($index === 0)
            {
                $inputAttr['checked'] = 'checked';
            }

            $collapse->add('input', $inputAttr);

            $collapseTitle = $collapse->add('div', ['class' => 'collapse-title font-semibold']);
            $collapseTitle->setText($item['title']);

            $collapseContent = $collapse->add('div', ['class' => 'collapse-content text-sm']);
            $collapseContent->setText($item['content']);
        }

        return $html->render();
    }

    public function alert(): string
    {
        $type = $this->attributes['type'] ?? 'info';
        $message = $this->attributes['message'] ?? '';
        $customSvg = $this->attributes['svgPath'] ?? '';

        $html = $this->htmlElement('div', [
            'role' => 'alert',
            'class' => "alert alert-{$type}"
        ]);

        $svgs = [
            'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
            'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ];
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-6 w-6 shrink-0 stroke-current">' . ($svgs[$type] ?? '') . '</svg>';

        if (!empty($customSvg))
        {
            $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-6 w-6 shrink-0 stroke-current">' . $customSvg . '</svg>';
        }

        $html->add('div')->setHtml($svgContent);
        $html->add('span')->setText($message);

        return $html->render();
    }

    public function breadcrumb(): string
    {
        $items = $this->attributes['items'] ?? [];

        $html = $this->htmlElement('div', ['class' => 'breadcrumbs text-sm']);

        $ul = $html->add('ul');

        foreach ($items as $item)
        {
            if (isset($item['url']))
            {
                $ul->add('li')->add('a', ['href' => $item['url']])
                    ->setText($item['label']);
            }
            else
            {
                $ul->add('li')->setText($item['label']);
            }
        }

        return $html->render();
    }
}
