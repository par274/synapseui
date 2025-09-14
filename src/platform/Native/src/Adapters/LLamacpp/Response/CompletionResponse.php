<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;
use Iterator;

/**
 * Response wrapper for the `/completion` or `/v1/completions` endpoint.
 *
 * Handles both synchronous and streaming responses.  When streamed, the
 * class implements {@see Iterator} to iterate over incoming chunks.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class CompletionResponse extends AbstractResponse implements Iterator
{
    /** @var bool Whether the response is a stream. */
    private readonly bool $isStream;

    /** @var string Buffer for accumulating streamed data. */
    private string $buffer = '';

    /** @var array Parsed lines from the buffer. */
    private array $lines = [];

    /** @var int Current position in the iterator. */
    private int $position = 0;

    /**
     * @param ResponseInterface $response
     * @param bool              $isStream
     */
    public function __construct(ResponseInterface $response, bool $isStream)
    {
        parent::__construct($response);
        $this->isStream = $isStream;
    }

    /** {@inheritDoc} */
    public function rewind(): void
    {
        $this->position = 0;
        $this->buffer   = '';
        $this->lines    = [];
    }

    /** {@inheritDoc} */
    public function current(): array|string|null
    {
        if ($this->isStream)
        {
            return $this->lines[$this->position] ?? null;
        }
        // Synchronous response: parse the whole body once.
        return $this->json();
    }

    /** {@inheritDoc} */
    public function key(): int
    {
        return $this->position;
    }

    /** {@inheritDoc} */
    public function next(): void
    {
        if ($this->isStream)
        {
            // Read 1 KiB at a time.
            $chunk = $this->psrResponse->getBody()->read(1024);
            if ($chunk === '')
            {
                return;
            }

            $this->buffer .= $chunk;

            while (($pos = strpos($this->buffer, "\n")) !== false)
            {
                $line   = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 1);

                if ($line === '')
                {
                    continue;
                }

                $decoded = json_decode(trim($line), true, 512, JSON_THROW_ON_ERROR);
                $this->lines[] = $decoded;
            }
        }

        ++$this->position;
    }

    /** {@inheritDoc} */
    public function valid(): bool
    {
        if ($this->isStream)
        {
            return isset($this->lines[$this->position]) ||
                !$this->psrResponse->getBody()->eof();
        }
        // For sync response, iterator is considered valid only once.
        return $this->position === 0;
    }

    /**
     * Return the generated text from a synchronous completion.
     *
     * @return string|null
     */
    public function text(): ?string
    {
        if ($this->isStream)
        {
            return null;
        }
        $json = $this->json();
        return $json['choices'][0]['text'] ?? null;
    }
}
