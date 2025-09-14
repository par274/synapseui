<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/generate` (single‑shot mode).
 */
final class GenerateCompletionResponse
{
    public string      $model;
    public string      $created_at;
    public ?string     $response = null;
    public bool        $done;

    /* Optional statistics – may be missing in some responses. */
    public ?int  $total_duration          = null;
    public ?int  $load_duration           = null;
    public ?int  $prompt_eval_count       = null;
    public ?int  $prompt_eval_duration    = null;
    public ?int  $eval_count              = null;
    public ?int  $eval_duration           = null;
    public array|null $context          = null;

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
