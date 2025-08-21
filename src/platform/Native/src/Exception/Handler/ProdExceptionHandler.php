<?php

namespace NativePlatform\Exception\Handler;

use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;
use NativePlatform\Templater\Engine as TemplateEngine;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

final class ProdExceptionHandler implements HandlerInterface
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
        $template = $this->templater->renderFromFile(
            'Errors/prod_blank.tpl',
            [
                'exception' => $e
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
}
