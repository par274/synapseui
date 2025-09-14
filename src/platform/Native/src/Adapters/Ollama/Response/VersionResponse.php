<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/version`.
 */
final class VersionResponse
{
    public string $version;

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        if (isset($data['version']))
        {
            $obj->version = $data['version'];
        }
        return $obj;
    }
}
