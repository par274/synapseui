<?php

namespace NativePlatform\Exception\Handler;

use NativePlatform\Scopes\RenderScope;

use Symfony\Component\HttpFoundation\Response;

use Throwable;

/**
 * Contract for every handler.
 *
 * @psalm-api
 */
interface HandlerInterface
{
    public const int DONE = 0; // continue chain
    public const int QUIT = 1; // stop chain
    public const int CONTINUE = 2; // continue but only special operations.

    /**
     * Handles the exception.
     *
     * @param Response $response
     * @param RenderScope $renderer
     * @param Throwable $e
     * @return int self::DONE | self::QUIT | self::CONTINUE
     */
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int;
}
