<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Response wrapper for the `all errors` endpoint.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
final class ErrorResponse extends AbstractResponse
{
    /**
     * Return full JSON payload of the health response.
     *
     * @return array
     */
    public function data(string $msg): string
    {
        return json_encode($msg);
    }
}
