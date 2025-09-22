<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp;

use Psr\Http\Message\ResponseInterface;
use JsonException;

/**
 * Real-time token-based chat stream
 *
 * Reads a Guzzle streaming response line-by-line, parses tokens from JSON,
 * and calls a callback for each token in real-time.
 */
final class TokenStreamReader
{
    private string $decoder = 'llamacpp';

    private ResponseInterface $response;
    private $streamResource;
    private string $buffer = '';
    private $stop = false;

    /** @var callable(array): void */
    private $callback;

    public function __construct(ResponseInterface $response, callable $callback)
    {
        $this->response = $response;
        $this->callback = $callback;
        $this->streamResource = $this->response->getBody()->detach();
        stream_set_blocking($this->streamResource, false);
    }

    public function start(): void
    {
        while (!$this->isEof() && !$this->stop)
        {
            $chunk = fread($this->streamResource, 1024);
            if ($chunk === false || $chunk === '')
            {
                usleep(50000);
                continue;
            }

            $this->buffer .= $chunk;

            while (($pos = strpos($this->buffer, "\n")) !== false)
            {
                $line = substr($this->buffer, 0, $pos);
                $this->buffer = substr($this->buffer, $pos + 1);

                $line = trim($line);
                if ($line === '') continue;

                if (str_starts_with($line, 'data:'))
                {
                    $line = substr($line, 5);
                }

                try
                {
                    $json = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

                    $chunk = json_encode($json, JSON_UNESCAPED_UNICODE);
                    $payload = "data: {$chunk}\n\n";
                    ($this->callback)($payload);

                    $finish = $json['choices'][0]['finish_reason'] ?? null;
                    if (in_array($finish, ['stop', 'length', 'content_filter'], true))
                    {
                        ($this->callback)("data: [DONE]\n\n");
                        $this->stop = true;
                        break;
                    }
                }
                catch (JsonException)
                {
                    $this->buffer = $line . "\n" . $this->buffer;
                    break;
                }
            }
        }
    }

    private function isEof(): bool
    {
        return feof($this->streamResource);
    }
}
