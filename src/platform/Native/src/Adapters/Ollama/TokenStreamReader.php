<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama;

use Psr\Http\Message\ResponseInterface;
use JsonException;

/**
 * Real-time token-based chat stream
 *
 * Reads a Guzzle streaming response line-by-line, parses tokens from JSON,
 * and calls a callback for each token in real-time.
 */
final class TokenStreamReader
{
    private ResponseInterface $response;
    private $streamResource;
    private string $buffer = '';

    /** 
     * @var callable(string): void
     */
    private $callback;

    /**
     * @param ResponseInterface $response
     * @param callable(string): void $callback
     */
    public function __construct(ResponseInterface $response, $callback)
    {
        $this->response = $response;
        $this->callback = $callback;
        $this->streamResource = $this->response->getBody()->detach();
        stream_set_blocking($this->streamResource, false);
    }

    public function start(): void
    {
        while (!$this->isEof())
        {
            $chunk = fread($this->streamResource, 1024);

            if ($chunk === false || $chunk === '')
            {
                usleep(50000);
                continue;
            }

            $this->buffer .= $chunk;

            while (($pos = strpos($this->buffer, "\n")) !== false)
            {
                $line = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 1);

                if ($trimmed = trim($line))
                {
                    try
                    {
                        $json = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                        if (isset($json['token']))
                        {
                            ($this->callback)($json['token']);
                        }
                    }
                    catch (\JsonException)
                    {
                        // ignore invalid JSON
                    }
                }
            }
        }
    }

    private function isEof(): bool
    {
        return feof($this->streamResource);
    }
}
