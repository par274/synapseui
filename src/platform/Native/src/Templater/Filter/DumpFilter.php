<?php

namespace NativePlatform\Templater\Filter;

class DumpFilter
{
    public static function compile(string $expr): string
    {
        return "\$this->echoRaw('<pre>') . var_dump({$expr}) . \$this->echoRaw('</pre>');";
    }

    public static function compileAlternative(string $expr): string
    {
        return "\\Symfony\\Component\\VarDumper\\VarDumper::dump({$expr});";
    }
}
