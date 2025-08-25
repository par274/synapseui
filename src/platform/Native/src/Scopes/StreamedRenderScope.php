<?php

namespace NativePlatform\Scopes;

use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedRenderScope
{
    private StreamedResponse $streamed;

    private array $headers = [
        'X-Accel-Buffering' => 'no',
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive'
    ];

    public function __construct()
    {
        $this->streamed = new StreamedResponse();
    }

    public function set(callable $callback, bool $isSend = false)
    {
        $this->streamed->setCallback(function () use ($callback): void
        {
            $callback(function ()
            {
                $this->flushBuffer();
            });

            echo "data: END-OF-STREAM\n\n";
            $this->flushBuffer();
        });

        foreach ($this->headers as $key => $val)
        {
            $this->streamed->headers->set($key, $val);
        }

        if ($isSend)
        {
            $this->streamed->send();
        }
    }

    /**
     * Send buffer info to page. If send() is needed and you are not in the controller, it provides standalone use.
     *
     * @return StreamedResponse
     */
    public function sendBuffer(): StreamedResponse
    {
        return $this->streamed->send();
    }

    public function flushBuffer()
    {
        if (ob_get_level()) ob_flush();
        flush();
    }
}
