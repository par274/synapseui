<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/ps`.
 */
final class RunningModelsResponse
{
    /** @var RunningModelInfo[] */
    public array $models = [];

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj      = new self();
        foreach ($data['models'] ?? [] as $m)
        {
            $obj->models[] = RunningModelInfo::fromArray($m);
        }
        return $obj;
    }
}

final class RunningModelInfo
{
    public string $name;
    public string $model;
    public int    $size;
    public string $digest;

    /** @var Details */
    public Details $details;

    public string $expires_at;
    public int    $size_vram;

    private function __construct()
    {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $k => $v)
        {
            if ($k === 'details')
            {
                $obj->details = Details::fromArray($v);
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
