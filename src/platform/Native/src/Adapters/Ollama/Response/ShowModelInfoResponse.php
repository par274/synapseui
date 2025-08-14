<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama\Response;

/**
 * Response for `/api/show`.
 */
final class ShowModelInfoResponse
{
    public string      $modelfile;
    public string      $parameters;
    public string      $template;

    /** @var Details */
    public Details   $details;

    /** @var array<string, mixed> */
    public array     $model_info = [];

    /** @var string[] */
    public array     $capabilities = [];

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
