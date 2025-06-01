<?php

namespace NativePlatform\Templater\Ast;

class Flattener
{
    protected $flat = [];

    public function collect(array $nodes, $templateName = '')
    {
        $this->flat[$templateName] = [];

        foreach ($nodes as $node)
        {
            $nodeArr = $node->toArray();

            if (isset($nodeArr['children']) && is_array($nodeArr['children']))
            {
                $children = $nodeArr['children'];
                unset($nodeArr['children']);
                $this->flat[$templateName][] = $nodeArr;
                $this->flat = array_merge($this->flat, self::collect($children));
            }
            elseif (isset($nodeArr['blocks']) && is_array($nodeArr['blocks']))
            {
                $blocks = $nodeArr['blocks'];
                unset($nodeArr['blocks']);
                $this->flat[$templateName][] = $nodeArr;
                foreach ($blocks as $blockName => $blockChildren)
                {
                    $this->flat[] = ['type' => 'block', 'name' => $blockName];
                    $this->flat = array_merge($this->flat, self::collect($blockChildren));
                }
            }
            else
            {
                $this->flat[$templateName][] = $nodeArr;
            }
        }
    }

    public function get()
    {
        return $this->flat;
    }
}
