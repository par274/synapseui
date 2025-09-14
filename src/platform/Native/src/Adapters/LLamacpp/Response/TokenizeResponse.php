<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `/tokenize` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class TokenizeResponse extends AbstractResponse
{
    /**
     * Return token IDs or objects with id/piece depending on `with_pieces`.
     *
     * @return array
     */
    public function tokens(): array
    {
        return $this->json()['tokens'] ?? [];
    }
}
