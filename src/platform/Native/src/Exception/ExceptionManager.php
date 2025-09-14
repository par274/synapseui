<?php

declare(strict_types=1);

namespace NativePlatform\Exception;

use NativePlatform\Exception\Handler\HandlerInterface;
use NativePlatform\Scopes\RenderScope;
use NativePlatform\Exception\Handler\LogHandler;

use Throwable;

use Symfony\Component\HttpFoundation\Response;

/**
 * Core of the library – manages a stack of handlers.
 *
 * @psalm-api
 */
final class ExceptionManager
{
    /** @var array<int, HandlerInterface> */
    private array $handlers = [];

    /** @var bool Is this manager registered as PHP exception handler? */
    private bool $isRegistered = false;

    /** @var bool $isLogGlobalFile */
    private bool $isLogGlobalFile = true;

    private string|null $forcePushHandler = null;

    /** @var Response $response */
    protected $response;

    /** @var RenderScope */
    protected $renderer;

    /**
     * __construct
     *
     * @param Response $response
     * @param RenderScope $renderer
     * @return void
     */
    public function __construct(Response $response, RenderScope $renderer)
    {
        $this->response = $response;
        $this->renderer = $renderer;
    }

    /**
     * Push a new handler to the top of the stack.
     *
     * @param HandlerInterface $handler
     * @return void
     */
    public function pushHandler(HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * Remove the top handler.
     *
     * @return HandlerInterface|null
     */
    public function popHandler(): ?HandlerInterface
    {
        return array_shift($this->handlers);
    }

    /**
     * Register this manager as PHP's exception handler.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->isRegistered)
        {
            return;
        }
        set_exception_handler([$this, 'handle']);
        $this->isRegistered = true;
    }

    /**
     * Unregister the custom exception handler.
     *
     * @return void
     */
    public function unregister(): void
    {
        if (!$this->isRegistered)
        {
            return;
        }
        restore_exception_handler();
        $this->isRegistered = false;
    }

    /**
     * If has a multiple handler, you can try this.
     *
     * @param mixed $handle
     * @return void
     */
    public function forcePushHandler(string|null $handle)
    {
        $this->forcePushHandler = $handle;
    }

    /**
     * Handle a Throwable – runs through all handlers.
     *
     * @param Throwable $e
     * @return void
     */
    public function handle(Throwable $e): void
    {
        if ($this->isLogGlobalFile)
        {
            $this->writeErrorLog($e);
        }

        foreach ($this->handlers as $handler)
        {
            if ($this->forcePushHandler !== null)
            {
                if ($handler instanceof $this->forcePushHandler || $handler instanceof LogHandler)
                {
                    $result = $handler->handle($this->response, $this->renderer, $e);

                    if ($result === HandlerInterface::QUIT)
                    {
                        return;
                    }
                }
            }
            else
            {
                $result = $handler->handle($this->response, $this->renderer, $e);

                if ($result === HandlerInterface::QUIT)
                {
                    return;
                }
            }
        }

        if (!headers_sent())
        {
            $this->response->setStatusCode(500);
            $this->renderer->finalRender('txt', "Unhandled exception of type " . get_class($e));
            $this->renderer->sendBuffer();
        }
    }

    /**
     * disableGlobalLog Disable write global php_error file.
     *
     * @return void
     */
    public function disableGlobalLog(): void
    {
        $this->isLogGlobalFile = false;
    }

    /**
     * writeErrorLog Write logs in global php_error file.
     *
     * @param  mixed $e
     * @return bool
     */
    private function writeErrorLog(Throwable $e): bool
    {
        $output = sprintf(
            "ERROR: %s\nMessage: %s\nFile: %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        return error_log($output);
    }
}
