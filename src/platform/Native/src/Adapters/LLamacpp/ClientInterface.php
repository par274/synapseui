<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\LLamacpp;

use NativePlatform\Adapters\LLamacpp\StreamIterator;
use NativePlatform\Adapters\LLamacpp\TokenStreamReader;
use NativePlatform\Adapters\LLamacpp\Response\HealthResponse;
use NativePlatform\Adapters\LLamacpp\Response\TokenizeResponse;
use NativePlatform\Adapters\LLamacpp\Response\DetokenizeResponse;
use NativePlatform\Adapters\LLamacpp\Response\EmbeddingResponse;
use NativePlatform\Adapters\LLamacpp\Response\ListModelsResponse;
use NativePlatform\Adapters\LLamacpp\Response\CompletionResponse;
use NativePlatform\Adapters\LLamacpp\Response\ChatCompletionResponse;

/**
 * Interface for the Llama.cpp client.
 *
 * @package NativePlatform\Adapters\LLamacpp
 */
interface ClientInterface
{
    /**
     * Check the health status of the llama.cpp server.
     *
     * @return HealthResponse Encapsulates server health information.
     */
    public function health(): HealthResponse;

    /**
     * Tokenize input text into model-specific token IDs.
     *
     * @param array $payload Payload containing input text.
     * @return TokenizeResponse Encapsulates tokenized IDs.
     */
    public function tokenize(array $payload): TokenizeResponse;

    /**
     * Convert token IDs back into text.
     *
     * @param array $payload Payload containing token IDs.
     * @return DetokenizeResponse Encapsulates reconstructed text.
     */
    public function detokenize(array $payload): DetokenizeResponse;

    /**
     * Generate embeddings for a given text.
     *
     * @param array $payload Payload containing text input.
     * @return EmbeddingResponse Encapsulates generated embeddings.
     */
    public function embed(array $payload): EmbeddingResponse;

    /**
     * Retrieve a list of all models available on the llama.cpp server.
     *
     * @return ListModelsResponse Contains information about available models.
     */
    public function listModels(): ListModelsResponse;

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
    public function completion(array $payload, bool $stream = false, ?callable $onToken = null): StreamIterator|TokenStreamReader|CompletionResponse|null;

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
    public function chat(array $payload, bool $stream = false, ?callable $onToken = null): StreamIterator|TokenStreamReader|ChatCompletionResponse|null;
}
