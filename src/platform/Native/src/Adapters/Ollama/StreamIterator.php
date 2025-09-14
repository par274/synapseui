<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama;

use Iterator;
use Psr\Http\Message\ResponseInterface;
use JsonException;
use GuzzleHttp\Psr7\Stream;

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
    /** @var array<int, array<string,mixed>> */
    private array $lines = [];
    private int $position = 0;
    private $streamResource;

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
        $this->fillBuffer();
    }

    /**
     * @return array<string,mixed>
     */
    public function current(): array
    {
        return $this->lines[$this->position];
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

    /**
     * Reads from the non-blocking stream and parses complete lines into JSON objects.
     */
    private function fillBuffer(): void
    {
        while (!isset($this->lines[$this->position]) && !$this->isEof())
        {
            $chunk = fread($this->streamResource, 1024);
            if ($chunk === false || $chunk === '')
            {
                // No data available yet, wait a tiny bit
                usleep(50000); // 50ms
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
                        $this->lines[] = json_decode(
                            $trimmed,
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        );
                    }
                    catch (JsonException $e)
                    {
                        // Ignore invalid JSON lines
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
