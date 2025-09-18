<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Base class for all response wrappers.
 *
 * @package NativePlatform\Adapters\LLamacpp\Response
 */
abstract class AbstractResponse
{
    /**
     * @var ResponseInterface
     */
    protected readonly ResponseInterface $psrResponse;

    public function __construct(ResponseInterface $response)
    {
        $this->psrResponse = $response;
    }

    /** Raw body as string. */
    public function getBody(): string
    {
        return (string)$this->psrResponse->getBody();
    }

    /**
     * Decode JSON body into associative array.
     *
     * @return array
     *
     * @throws \JsonException If the JSON is invalid.
     */
    public function json(): array|string
    {
        if (json_validate($this->getBody()))
        {
            return json_decode($this->getBody(), true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->getBody();
    }
}
