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
}
