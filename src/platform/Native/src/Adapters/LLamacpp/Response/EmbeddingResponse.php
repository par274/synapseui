<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `/embedding` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class EmbeddingResponse extends AbstractResponse
{
    /**
     * Return embedding vector(s) as array of floats.
     *
     * @return array
     */
    public function embeddings(): array
    {
        return $this->json()['embeddings'] ?? [];
    }
}
