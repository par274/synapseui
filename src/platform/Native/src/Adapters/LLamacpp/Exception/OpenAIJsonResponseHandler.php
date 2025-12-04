<?php

namespace NativePlatform\Adapters\LLamacpp\Exception;

use NativePlatform\Exception\Handler\AdapterNotWorkingException;
use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

final class OpenAIJsonResponseHandler implements HandlerInterface
{
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        if (!($e instanceof AdapterNotWorkingException))
        {
            return HandlerInterface::CONTINUE;
        }

        $payload = [
            'error' => [
                'message' => $e->getMessage() ?: 'The server is currently unavailable.',
                'type' => 'service_unavailable',
                'param' => null,
                'code' => 'connect_error',
            ]
        ];

        $renderer->finalRender('json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $response->setStatusCode(503);
        $renderer->sendBuffer();

        return HandlerInterface::QUIT; // stop chain
    }
}
