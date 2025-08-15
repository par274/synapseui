<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp;

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

    private string $accumulatedContent = '';

    public function __construct(ResponseInterface $response, callable $callback)
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

                $line = trim($line);
                if ($line === '') continue;

                if (str_starts_with($line, 'data:'))
                {
                    $line = substr($line, 5);
                }

                try
                {
                    $decoded = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

                    $choice = $decoded['choices'][0] ?? null;
                    if (!$choice) continue;

                    if (isset($choice['delta']['role']))
                    {
                        $role = $choice['delta']['role'];
                        continue;
                    }

                    $deltaContent = $choice['delta']['content'] ?? null;
                    if ($deltaContent !== null)
                    {
                        $this->accumulatedContent .= $deltaContent;
                        ($this->callback)($deltaContent);
                    }

                    if (($choice['finish_reason'] ?? null) === 'stop')
                    {
                        return;
                    }
                }
                catch (JsonException)
                {
                    // ignore invalid lines
                }
            }
        }
    }

    private function isEof(): bool
    {
        return feof($this->streamResource);
    }
}
