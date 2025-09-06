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
    private $stop = false;

    /** @var callable(array): void */
    private $callback;

    public function __construct(ResponseInterface $response, callable $callback)
    {
        $this->response = $response;
        $this->callback = $callback;
        $this->streamResource = $this->response->getBody()->detach();
        stream_set_blocking($this->streamResource, false);
    }

    public function start(): void
    {
        while (!$this->isEof() && !$this->stop)
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
                        $jsonData = "data: " . json_encode($json, JSON_UNESCAPED_UNICODE) . "\n\n";

                        ($this->callback)($jsonData);

                        if (($json['done_reason'] ?? null) === 'stop')
                        {
                            $this->stop = true;
                            break;
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
