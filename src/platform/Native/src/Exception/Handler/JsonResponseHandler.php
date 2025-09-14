<?php

namespace NativePlatform\Exception\Handler;

use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

/**
 * Returns exception details as JSON.
 *
 * @psalm-api
 */
final class JsonResponseHandler implements HandlerInterface
{
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        // Build a minimal payload
        $payload = [
            'error' => [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]
        ];
        $output = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if (!headers_sent())
        {
            $response->setStatusCode(500);
            $renderer->finalRender('json', $output);
            $renderer->sendBuffer();
        }

        return self::QUIT; // stop chain
    }
}
