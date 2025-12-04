<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp;

use NativePlatform\Adapters\AdapterClient;
use NativePlatform\Exception\Handler\AdapterNotWorkingException;
use NativePlatform\Adapters\LLamacpp\{
    StreamIterator,
    TokenStreamReader,
    ClientInterface,
    Response\HealthResponse,
    Response\TokenizeResponse,
    Response\DetokenizeResponse,
    Response\EmbeddingResponse,
    Response\ListModelsResponse,
    Response\CompletionResponse,
    Response\ChatCompletionResponse
};

use GuzzleHttp\Exception\{
    GuzzleException,
    RequestException,
    ConnectException
};

use Psr\Http\Message\ResponseInterface;

/** @example for llama.cpp */
/**
 * -------------------------------
 * 1. Health check
 * -------------------------------
 *
 * @example
 * $health = $client->health();
 * echo $health->getStatus(); // "ok"
 */

/**
 * -------------------------------
 * 2. Tokenize text
 * -------------------------------
 *
 * @example
 * $tokenResponse = $client->tokenize(['text' => 'Hello world']);
 * print_r($tokenResponse->getTokens());
 */

/**
 * -------------------------------
 * 3. Detokenize tokens
 * -------------------------------
 *
 * @example
 * $textResponse = $client->detokenize(['tokens' => [15496, 995]]);
 * echo $textResponse->getText(); // "Hello world"
 */

/**
 * -------------------------------
 * 4. Generate embeddings
 * -------------------------------
 *
 * @example
 * $embeddingResponse = $client->embed(['input' => 'AI is amazing']);
 * print_r($embeddingResponse->getVector());
 */

/**
 * -------------------------------
 * 5. List all available models
 * -------------------------------
 *
 * @example
 * $models = $client->listModels();
 * print_r($models->getModels());
 */

/**
 * -------------------------------
 * 6. Completion
 * -------------------------------
 *
 * 6a. Non-streaming completion
 * @example
 * $completion = $client->completion([
 *     'model' => 'llama-2-7b',
 *     'prompt' => 'Write a short poem about AI.'
 * ]);
 * echo $completion->getText();
 *
 * 6b. Streaming completion
 * @example
 * $stream = $client->completion([
 *     'model' => 'llama-2-7b',
 *     'prompt' => 'Describe the future of AI.'
 * ], true);
 *
 * foreach ($stream as $chunk) {
 *     echo $chunk;
 * }
 *
 * 6c. Token-level streaming completion with callback
 * @example
 * $client->completion([
 *     'model' => 'llama-2-7b',
 *     'prompt' => 'Explain quantum computing in simple terms.'
 * ], true, function(string $token) {
 *     echo $token;
 * });
 */

/**
 * -------------------------------
 * 7. Chat completion
 * -------------------------------
 *
 * 7a. Non-streaming chat completion
 * @example
 * $chat = $client->chat([
 *     'model' => 'llama-2-7b',
 *     'messages' => [
 *         ['role' => 'user', 'content' => 'Hello, how are you?']
 *     ]
 * ]);
 * echo $chat->getMessage();
 *
 * 7b. Streaming chat completion
 * @example
 * $streamChat = $client->chat([
 *     'model' => 'llama-2-7b',
 *     'messages' => [
 *         ['role' => 'user', 'content' => 'Tell me a story about a robot.']
 *     ]
 * ], true);
 *
 * foreach ($streamChat as $chunk) {
 *     echo $chunk;
 * }
 *
 * 7c. Token-level streaming chat completion with callback
 * @example
 * $client->chat([
 *     'model' => 'llama-2-7b',
 *     'messages' => [
 *         ['role' => 'user', 'content' => 'Write a funny joke about AI.']
 *     ]
 * ], true, function(string $token) {
 *     echo $token;
 * });
 */

/**
 * Concrete implementation of {@see LlamacppClientInterface} using Guzzle.
 *
 * Provides methods for interacting with a llama.cpp server including health checks,
 * tokenization, embeddings, model listing, and text completions.
 *
 * Supports both streaming and non-streaming responses, with optional token-level
 * callback streaming for real-time consumption.
 *
 * @package NativePlatform\Adapters\LLamacpp
 */
final class Client extends AdapterClient implements ClientInterface
{
    protected string $utilization = 'cuda';

    /**
     * Internal helper that wraps the raw Guzzle request and handles exceptions.
     *
     * @param string $method HTTP method (GET, POST, DELETE, etc.)
     * @param string $uri API endpoint URI
     * @param array $options Request options (headers, json payload, etc.)
     * @return ResponseInterface
     * @throws \RuntimeException On Guzzle request failure
     */
    private function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        try
        {
            return $this->client->request($method, $uri, $options);
        }
        catch (ConnectException $e)
        {
            throw new AdapterNotWorkingException(
                $e->getMessage(),
                0,
                $e
            );
        }
        catch (RequestException $e)
        {
            throw new AdapterNotWorkingException(
                $e->getMessage(),
                0,
                $e
            );
        }
        catch (GuzzleException $e)
        {
            throw new AdapterNotWorkingException(
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Check the health status of the llama.cpp server.
     *
     * @return HealthResponse Encapsulates server health information.
     */
    public function health(): HealthResponse
    {
        $resp = $this->request('GET', '/health');

        return new HealthResponse($resp);
    }

    /**
     * Tokenize input text into model-specific token IDs.
     *
     * @param array $payload Payload containing input text.
     * @return TokenizeResponse Encapsulates tokenized IDs.
     */
    public function tokenize(array $payload): TokenizeResponse
    {
        $options = ['json' => $payload];
        $resp = $this->request('POST', '/tokenize', $options);

        return new TokenizeResponse($resp);
    }

    /**
     * Convert token IDs back into text.
     *
     * @param array $payload Payload containing token IDs.
     * @return DetokenizeResponse Encapsulates reconstructed text.
     */
    public function detokenize(array $payload): DetokenizeResponse
    {
        $options = ['json' => $payload];
        $resp = $this->request('POST', '/detokenize', $options);

        return new DetokenizeResponse($resp);
    }

    /**
     * Generate embeddings for a given text.
     *
     * @param array $payload Payload containing text input.
     * @return EmbeddingResponse Encapsulates generated embeddings.
     */
    public function embed(array $payload): EmbeddingResponse
    {
        $options = ['json' => $payload];
        $resp = $this->request('POST', '/v1/embeddings', $options);

        return new EmbeddingResponse($resp);
    }

    /**
     * Retrieve a list of all models available on the llama.cpp server.
     *
     * @return ListModelsResponse Contains information about available models.
     */
    public function listModels(): ListModelsResponse
    {
        $resp = $this->request('GET', '/v1/models');

        return new ListModelsResponse($resp);
    }

    /**
     * Generate a text completion from the model.
     *
     * Supports both streaming and non-streaming responses:
     * - If `$stream` is true and `$onToken` callback is provided, token-level streaming
     *   is performed and the method returns null.
     * - If `$stream` is true and no callback is provided, a StreamIterator is returned.
     * - If `$stream` is false, a CompletionResponse containing the full completion is returned.
     *
     * @param array $payload API payload including model, prompt, and other options.
     * @param bool $stream Whether to stream the response.
     * @param callable(string): void|null $onToken Optional callback invoked for each token during streaming.
     * @return StreamIterator|TokenStreamReader|CompletionResponse|null
     */
    public function completion(array $payload, bool $stream = false, ?callable $onToken = null): StreamIterator|TokenStreamReader|CompletionResponse|null
    {
        $payload['model'] = "{$payload['model']}_{$this->utilization}";
        $options = ['json' => $payload];

        if ($stream && $onToken !== null)
        {
            $options['json']['stream'] = true;
            $options['stream'] = true;
            $resp = $this->request('POST', '/v1/completions', $options);

            $tokenReader = new TokenStreamReader($resp, $onToken);
            $tokenReader->start();

            return null;
        }

        if ($stream)
        {
            $options['json']['stream'] = true;
            $options['stream'] = true;
            $resp = $this->request('POST', '/v1/completions', $options);

            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/v1/completions', $options);

        return new CompletionResponse($resp, false);
    }

    /**
     * Interact with the model in a chat-style interface.
     *
     * Supports both streaming and non-streaming responses:
     * - If `$stream` is true and `$onToken` callback is provided, token-level streaming
     *   is performed and the method returns null.
     * - If `$stream` is true and no callback is provided, a StreamIterator is returned.
     * - If `$stream` is false, a ChatCompletionResponse containing the full conversation is returned.
     *
     * @param array $payload API payload including model and chat messages.
     * @param bool $stream Whether to stream the response.
     * @param callable(string): void|null $onToken Optional callback invoked for each token during streaming.
     * @return StreamIterator|TokenStreamReader|ChatCompletionResponse|null
     */
    public function chat(array $payload, bool $stream = false, ?callable $onToken = null): StreamIterator|TokenStreamReader|ChatCompletionResponse|null
    {
        $payload['model'] = "{$payload['model']}_{$this->utilization}";
        $options = ['json' => $payload];

        if ($stream && $onToken !== null)
        {
            $options['json']['stream'] = true;
            $options['stream'] = true;
            $resp = $this->request('POST', '/v1/chat/completions', $options);

            $tokenReader = new TokenStreamReader($resp, $onToken);
            $tokenReader->start();

            return null;
        }

        if ($stream)
        {
            $options['json']['stream'] = true;
            $options['stream'] = true;
            $resp = $this->request('POST', '/v1/chat/completions', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/v1/chat/completions', $options);

        return new ChatCompletionResponse($resp, false);
    }

    /**
     * Use CPU instead of NVIDIA GPU.
     *
     * @return void
     */
    public function useCpu()
    {
        $this->utilization = 'cpu';
    }

    /**
     * Force use GPU instead of CPU.
     *
     * @return void
     */
    public function useForceGPU()
    {
        $this->utilization = 'cuda';
    }
}
