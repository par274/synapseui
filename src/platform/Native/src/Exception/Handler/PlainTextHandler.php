<?php

namespace NativePlatform\Exception\Handler;

use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

final class PlainTextHandler implements HandlerInterface
{
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        // Build plain-text message
        $output = sprintf(
            "ERROR: %s\nMessage: %s\nFile: %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        if (PHP_SAPI === 'cli')
        {
            fwrite(STDERR, $output . PHP_EOL);
        }
        else
        {
            if (!headers_sent())
            {
                $response->setStatusCode(500);
                $renderer->finalRender('html', nl2br($output) . "<br>");
                $renderer->sendBuffer();
            }
        }

        return self::QUIT;
    }
}
