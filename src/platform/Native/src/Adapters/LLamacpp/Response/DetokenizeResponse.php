<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `/detokenize` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class DetokenizeResponse extends AbstractResponse
{
    /**
     * Return reconstructed text from token IDs.
     *
     * @return string
     */
    public function content(): string
    {
        return $this->json()['content'] ?? '';
    }
}
