<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;
use Iterator;

/**
 * Response wrapper for the `/v1/chat/completions` endpoint.
 *
 * Handles both synchronous and streaming responses.
 */
final class ChatCompletionResponse extends AbstractResponse implements Iterator
{
    /** @var bool Whether the response is a stream */
    private readonly bool $isStream;

    /** @var string Buffer for accumulating streamed data */
    private string $buffer = '';

    /** @var array Parsed lines from the buffer */
    private array $lines = [];

    /** @var int Iterator index */
    private int $position = 0;

    /** Collected choices (for non-stream sync responses) */
    public array $choices = [];

    public string $id = '';
    public string $model = '';
    public string|int $created = 0;
    public string $object = 'chat.completion';
    public ?array $usage = null;

    public function __construct(ResponseInterface $response, bool $isStream)
    {
        parent::__construct($response);
        $this->isStream = $isStream;

        // hydrate immediately for synchronous response
        if (!$isStream)
        {
            $this->hydrate($this->json());
        }
    }

    /** Hydrate object from array for sync response */
    private function hydrate(array $data): void
    {
        foreach ($data as $k => $v)
        {
            if ($k === 'choices' && is_array($v))
            {
                foreach ($v as $choiceData)
                {
                    $this->choices[] = ChatChoice::fromArray($choiceData);
                }
                continue;
            }
            if (property_exists($this, $k))
            {
                $this->$k = $v;
            }
        }
    }

    /** Iterator: rewind stream */
    public function rewind(): void
    {
        $this->position = 0;
        $this->buffer   = '';
        $this->lines    = [];
    }

    /** Iterator: current item */
    public function current(): array|null
    {
        if ($this->isStream)
        {
            return $this->lines[$this->position] ?? null;
        }
        return $this->json();
    }

    /** Iterator: key */
    public function key(): int
    {
        return $this->position;
    }

    /** Iterator: advance */
    public function next(): void
    {
        if ($this->isStream)
        {
            // read chunk
            $chunk = $this->psrResponse->getBody()->read(1024);
            if ($chunk === '')
            {
                return;
            }

            $this->buffer .= $chunk;

            // SSE format typically "data: {json}\n\n"
            while (($pos = strpos($this->buffer, "\n")) !== false)
            {
                $line        = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 1);

                $line = trim($line);
                if ($line === '' || !str_starts_with($line, 'data:'))
                {
                    continue;
                }

                $payload = substr($line, 5);
                if ($payload === '[DONE]')
                {
                    $this->lines[] = ['done' => true];
                    continue;
                }

                $decoded = json_decode($payload, true);
                if ($decoded !== null)
                {
                    $this->lines[] = $decoded;
                }
            }
        }

        ++$this->position;
    }

    /** Iterator: validity check */
    public function valid(): bool
    {
        if ($this->isStream)
        {
            return isset($this->lines[$this->position])
                || !$this->psrResponse->getBody()->eof();
        }
        return $this->position === 0; // sync valid only once
    }

    /** Helper to get assistant message text (sync only) */
    public function content(): ?string
    {
        if ($this->isStream)
        {
            return null;
        }
        return $this->choices[0]->message->content ?? null;
    }
}

/**
 * One choice in a chat completion.
 */
final class ChatChoice
{
    public int $index;
    public ?string $finish_reason = null;
    public ChatMessage $message;

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $k => $v)
        {
            if ($k === 'message')
            {
                $obj->message = ChatMessage::fromArray($v);
                continue;
            }
            if (property_exists($obj, $k))
            {
                $obj->$k = $v;
            }
        }
        return $obj;
    }
}

/**
 * Simple chat message object.
 */
final class ChatMessage
{
    public string $role;
    public ?string $content = null;

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $k => $v)
        {
            if (property_exists($obj, $k))
            {
                $obj->$k = $v;
            }
        }
        return $obj;
    }
}
