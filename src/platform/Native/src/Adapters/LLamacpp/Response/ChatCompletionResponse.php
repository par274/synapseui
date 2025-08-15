<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

/**
 * Response for `/v1/chat/completions` (single-shot mode).
 */
final class ChatCompletionResponse
{
    /** ID of the request */
    public string $id;

    /** Model name */
    public string $model;

    /** Creation timestamp (unix or ISO8601 depending on llama.cpp build) */
    public string|int $created;

    /** Object type, e.g. 'chat.completion' */
    public string $object;

    /** @var ChatChoice[] */
    public array $choices = [];

    /** @var array<string, mixed>|null */
    public ?array $usage = null;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $k => $v)
        {
            if ($k === 'choices' && is_array($v))
            {
                foreach ($v as $choiceData)
                {
                    $obj->choices[] = ChatChoice::fromArray($choiceData);
                }
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
 * One choice in a chat completion.
 */
final class ChatChoice
{
    /** Index of the choice */
    public int $index;

    /** Finish reason, e.g. 'stop' */
    public ?string $finish_reason = null;

    /** Chat message returned */
    public ChatMessage $message;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
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
 * Simple representation of a chat message.
 */
final class ChatMessage
{
    public string $role;
    public ?string $content = null;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
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
