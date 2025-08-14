<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/pull`. When streaming, each line is a status object; the final line contains `"status":"success"`.
 */
final class PullModelResponse
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
