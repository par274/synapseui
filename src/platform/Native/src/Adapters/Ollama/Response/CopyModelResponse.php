<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/copy`. The API returns a plain JSON object with a single `status` key.
 */
final class CopyModelResponse
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
