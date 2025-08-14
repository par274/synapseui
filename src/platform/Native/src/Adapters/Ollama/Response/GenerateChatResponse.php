<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/chat` (single‑shot mode).
 */
final class GenerateChatResponse
{
    public string     $model;
    public string     $created_at;

    /** @var ChatMessage */
    public ChatMessage $message;

    public bool        $done;

    /* Optional statistics – may be missing in some responses. */
    public ?int  $total_duration          = null;
    public ?int  $load_duration           = null;
    public ?int  $prompt_eval_count       = null;
    public ?int  $prompt_eval_duration    = null;
    public ?int  $eval_count              = null;
    public ?int  $eval_duration           = null;

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
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
    public string      $role;
    public ?string     $content   = null; // may be null if only tool_calls are present
    public array|null  $tool_calls = null;
    public array|null  $images     = null;

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
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
