<?php

namespace NativePlatform\Exception\Handler;

use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\Escaper;
use NativePlatform\Scopes\RenderScope;
use NativePlatform\Templater\Engine as TemplateEngine;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

/**
 * Renders a full HTML page with exception details.
 *
 * @psalm-api
 */
final class PrettyPageHandler implements HandlerInterface
{
    /**
     * @var TemplateEngine
     */
    private $templater;

    /**
     * Creates a new {@see ExceptionHandler}.
     *
     * @param TemplateEngine $templater The template engine used to render the error page.
     * @param RenderScope $renderer  The renderer responsible for outputting the final response.
     */
    public function __construct(TemplateEngine $templater)
    {
        $this->templater = $templater;
    }

    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        $file  = $e->getFile();
        $line  = (int)$e->getLine();
        $displayLines = $this->buildDisplayLines($file, $line, 10);

        $template = $this->templater->renderFromFile(
            'Errors/exception.tplx',
            [
                'exception'      => $e,
                'displayLines'   => $displayLines,
                'traceAsString'  => Escaper::rawEscaper($e->getTraceAsString()),
            ]
        );

        if (!headers_sent())
        {
            $response->setStatusCode(500);
            $renderer->finalRender('html', $template);
            $renderer->sendBuffer();
        }

        return self::QUIT; // stop chain
    }

    /**
     * Builds an array of code lines with syntax highlighting around the error line.
     *
     * @param string $file      Path to the file containing the exception.
     * @param int    $errorLine Line number where the exception occurred.
     * @param int    $padding   Number of lines before and after the error line to display (default 5).
     *
     * @return array Array of associative arrays, each with keys:
     *               - 'lineNumber'  (int)
     *               - 'code'        (string) highlighted PHP code
     *               - 'isErrorLine' (bool) true if this is the line where the error occurred.
     */
    protected function buildDisplayLines(string $file, int $errorLine, int $padding = 5): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if (!$lines)
        {
            return [];
        }

        $start = max(1, $errorLine - $padding);
        $end   = min(count($lines), $errorLine + $padding);

        $result = [];
        for ($i = $start; $i <= $end; $i++)
        {
            $code = $lines[$i - 1];

            $highlighted = highlight_string("<?php " . $code, true);
            $highlighted = str_replace('&lt;?php', '', $highlighted);

            $highlighted = preg_replace_callback(
                '~<span style="color:\s*(#[0-9A-Fa-f]+)">(.+?)</span>~',
                function ($m)
                {
                    $map = [
                        '#0000BB' => 'keyword', // function, class, echo
                        '#007700' => 'default', // normal text
                        '#DD0000' => 'string', // string
                        '#FF8000' => 'comment', // comment
                    ];
                    $cls = $map[strtoupper($m[1])] ?? 'default';
                    return "<span class=\"{$cls}\">{$m[2]}</span>";
                },
                $highlighted
            );

            $result[] = [
                'lineNumber' => $i,
                'code' => $highlighted,
                'isErrorLine' => ($i === $errorLine),
            ];
        }

        return $result;
    }
}
