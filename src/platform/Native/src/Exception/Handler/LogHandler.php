<?php

namespace NativePlatform\Exception\Handler;

use Symfony\Component\HttpFoundation\Response;
use NativePlatform\Scopes\RenderScope;

use Psr\Log\LoggerInterface;

use Throwable;

/**
 * Logs the exception using any PSR‑3 compliant logger.
 *
 * @psalm-api
 */
final class LogHandler implements HandlerInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles the exception by writing a log entry.
     *
     * Returns **false** so that other handlers (e.g. PrettyPage) can still run.
     */
    public function handle(Response $response, RenderScope $renderer, Throwable $e): int
    {
        // Short message for the log file
        $message = sprintf(
            'Uncaught %s: "%s" in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        // Context can include the whole exception object & trace
        $context = [
            'exception' => $e,
            'trace' => $e->getTrace(),
        ];

        // PSR‑3 logger – we use `error` level by default.
        $this->logger->error($message, $context);

        // Don't stop the chain: let PrettyPage / JsonResponse still render
        return self::DONE;
    }
}
