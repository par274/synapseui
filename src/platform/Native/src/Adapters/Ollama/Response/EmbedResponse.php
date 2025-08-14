<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/embed`.
 */
final class EmbedResponse
{
    public string      $model;
    /** @var float[][] */
    public array       $embeddings;

    public int         $total_duration;
    public int         $load_duration;
    public int         $prompt_eval_count;

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
