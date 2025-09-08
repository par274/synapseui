<?php

namespace NativePlatform\Scopes;

use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;

class RenderScope
{
    /** @var Response */
    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Renders the provided content with an appropriate Content‑Type header.
     *
     * @param string $type
     * @param array|string $content The body to send back.
     * @return void
     */
    public function finalRender(string $type, array|string $content): void
    {
        $contentType = [
            'type' => 'Content-Type',
            'mime' => match ($type)
            {
                'html' => 'text/html; charset=utf-8',
                'json' => 'application/json; charset=utf-8',
                'xml'  => 'application/xml; charset=utf-8',
                'txt'  => 'text/plain; charset=utf-8',
                default => throw new InvalidArgumentException("Unsupported render type: {$type}")
            }
        ];

        if (str_starts_with($contentType['mime'], 'application/json'))
        {
            $content = json_encode($content);
        }

        $this->response->headers->set($contentType['type'], $contentType['mime']);
        $this->response->setContent($content);

        return;
    }

    /**
     * Send buffer info to page. If send() is needed and you are not in the controller, it provides standalone use.
     *
     * @return Response
     */
    public function sendBuffer(): Response
    {
        return $this->response->send();
    }
}
