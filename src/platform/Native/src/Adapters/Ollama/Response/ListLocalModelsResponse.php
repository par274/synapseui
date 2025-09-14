<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/tags`.
 */
final class ListLocalModelsResponse
{
    /** @var ModelInfo[] */
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
            $obj->models[] = ModelInfo::fromArray($m);
        }
        return $obj;
    }
}

final class ModelInfo
{
    public string $name;
    public string $model;
    public string $modified_at;
    public int    $size;
    public string $digest;

    /** @var Details */
    public Details $details;

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

final class Details
{
    public string $parent_model   = '';
    public string $format         = '';
    public string $family         = '';
    /** @var string[] */
    public array  $families       = [];
    public string $parameter_size = '';
    public string $quantization_level = '';

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
