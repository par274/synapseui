<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/push`. Streaming format is identical to pull – each line a status object.
 */
final class PushModelResponse
{
    public string $status;

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        if (isset($data['status']))
        {
            $obj->status = $data['status'];
        }
        return $obj;
    }
}
