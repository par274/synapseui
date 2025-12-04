<?php

namespace NativePlatform\Adapters\LLamacpp\Exception;

use NativePlatform\Exception\Handler\AdapterNotWorkingException;
use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

final class OpenAIJsonResponseHandler extends AdapterNotWorkingException implements HandlerInterface
{
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        $payload = [
            'error' => [
                'message' => 'The server is currently unavailable.',
                'type' => 'service_unavailable',
                'param' => null,
                'code' => 'server_error',
            ]
        ];

        $renderer->finalRender('json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $response->setStatusCode(503);
        $renderer->sendBuffer();

        return HandlerInterface::QUIT;
    }
}
