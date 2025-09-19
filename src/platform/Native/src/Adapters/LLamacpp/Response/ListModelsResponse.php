<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `/v1/models` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class ListModelsResponse extends AbstractResponse
{
    /**
     * Return the array of models returned by the API.
     *
     * The structure matches the OpenAI “list models” schema:
     * [
     *   "object" => "list",
     *   "data"   => [ /* model objects *\/ ]
     * ]
     *
     * @return array
     */
    public function data(): array
    {
        return $this->json();
    }

    /**
     * Shortcut to the actual list of model objects.
     *
     * @return array
     */
    public function models(): array
    {
        return $this->json()['data'] ?? [];
    }

    /**
     * Find a single model by its id.
     *
     * @param string $id
     * @return array|null
     */
    public function findModel(string $id): ?array
    {
        $filtered = array_filter($this->models(), function ($model) use ($id)
        {
            return isset($model['id']) && $model['id'] === $id;
        });

        // array_filter returns an array with preserved keys, so we need reset()
        return $filtered ? reset($filtered) : null;
    }
}
