<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/delete`. The API simply returns a JSON object with the final status.
 */
final class DeleteModelResponse
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
