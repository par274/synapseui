<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp;

use Iterator;
use Psr\Http\Message\ResponseInterface;
use JsonException;

/**
 * StreamIterator
 *
 * Reads a Guzzle HTTP streaming response line-by-line, JSON-decodes each line, and yields the result.
 * Works for streaming endpoints that return newline-delimited JSON (NDJSON).
 *
 * Features:
 *  - Non-blocking read using fread()
 *  - Cross-platform (no stream_select warnings)
 *  - Handles partial lines until they are complete
 *  - Yields each complete JSON object as soon as it is available
 */
final class StreamIterator implements Iterator
{
    private readonly ResponseInterface $response;
    private string $buffer = '';
    private array $lines = [];
    private int $position = 0;
    private $streamResource;

    private string $accumulatedContent = '';

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->streamResource = $this->response->getBody()->detach();
        stream_set_blocking($this->streamResource, false);
    }

    public function rewind(): void
    {
        $this->position = 0;
        $this->buffer = '';
        $this->lines = [];
        $this->accumulatedContent = '';
        $this->fillBuffer();
    }

    public function current(): array
    {
        return $this->lines[$this->position] ?? [];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
        if (!isset($this->lines[$this->position]))
        {
            $this->fillBuffer();
        }
    }

    public function valid(): bool
    {
        return isset($this->lines[$this->position]) || !$this->isEof();
    }

    private function fillBuffer(): void
    {
        while (!isset($this->lines[$this->position]) && !$this->isEof())
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
                    $deltaContent = $choice['delta']['content'] ?? '';

                    if ($deltaContent !== '')
                    {
                        $this->accumulatedContent .= $deltaContent;
                    }

                    $this->lines[] = [
                        'raw' => $decoded,
                        'content' => $this->accumulatedContent,
                        'finish_reason' => $choice['finish_reason'] ?? null,
                    ];

                    if (($choice['finish_reason'] ?? null) === 'stop')
                    {
                        break 2;
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
