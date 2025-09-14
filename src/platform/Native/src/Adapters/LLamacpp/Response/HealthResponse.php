<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `/health` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class HealthResponse extends AbstractResponse
{
    /**
     * Indicates whether the model is ready to serve requests.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->psrResponse->getStatusCode() === 200;
    }

    /**
     * Return full JSON payload of the health response.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->json();
    }
}
