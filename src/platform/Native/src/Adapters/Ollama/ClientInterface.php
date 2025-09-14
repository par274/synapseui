<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama;

use NativePlatform\Adapters\Ollama\Response\GenerateCompletionResponse;
use NativePlatform\Adapters\Ollama\Response\GenerateChatResponse;
use NativePlatform\Adapters\Ollama\Response\CreateModelResponse;
use NativePlatform\Adapters\Ollama\Response\ListLocalModelsResponse;
use NativePlatform\Adapters\Ollama\Response\ShowModelInfoResponse;
use NativePlatform\Adapters\Ollama\Response\CopyModelResponse;
use NativePlatform\Adapters\Ollama\Response\DeleteModelResponse;
use NativePlatform\Adapters\Ollama\Response\PullModelResponse;
use NativePlatform\Adapters\Ollama\Response\PushModelResponse;
use NativePlatform\Adapters\Ollama\Response\EmbedResponse;
use NativePlatform\Adapters\Ollama\Response\RunningModelsResponse;
use NativePlatform\Adapters\Ollama\Response\VersionResponse;
use NativePlatform\Adapters\Ollama\StreamIterator;
use NativePlatform\Adapters\Ollama\TokenStreamReader;

use Iterator;

/**
 * Public contract for an Ollama client.
 *
 * Each operation returns either a concrete response object or an iterator that yields
 * incremental results (for streaming endpoints). Use `$stream` for streaming and
 * `$onToken` callback for token-based streaming.
 */
interface ClientInterface
{
    /**
     * Generate a text completion from a model.
     *
     * Example:
     * ```php
     * foreach ($client->generate(['model' => 'llama3.2', 'prompt' => 'Hello'], true) as $chunk) {
     *     echo $chunk;
     * }
     * ```
     *
     * @param array $payload The API request payload.
     * @param bool $stream Whether to stream the response.
     * @param callable|null $onToken Optional callback for token streaming.
     * @return Iterator|TokenStreamReader|StreamIterator|GenerateCompletionResponse|null
     */
    public function generate(array $payload, bool $stream = true, ?callable $onToken = null): Iterator|TokenStreamReader|StreamIterator|GenerateCompletionResponse|null;

    /**
     * Chat with a model.
     *
     * Example:
     * ```php
     * foreach ($client->chat(['model' => 'llama3.2', 'messages' => [['role' => 'user', 'content' => 'Hi']]], true) as $chunk) {
     *     echo $chunk;
     * }
     * ```
     *
     * @param array $payload The chat request payload.
     * @param bool $stream Whether to stream the response.
     * @param callable|null $onToken Optional callback for token streaming.
     * @return Iterator|TokenStreamReader|StreamIterator|GenerateChatResponse|null
     */
    public function chat(array $payload, bool $stream = true, ?callable $onToken = null): Iterator|TokenStreamReader|StreamIterator|GenerateChatResponse|null;

    /**
     * Create a new model from an existing one.
     *
     * Example:
     * ```php
     * $client->createModel(['model' => 'mario', 'from' => 'llama3.2', 'system' => 'You are Mario']);
     * ```
     *
     * @param array $payload Model creation payload.
     * @param bool $stream Whether to stream the response.
     * @return Iterator|StreamIterator|CreateModelResponse
     */
    public function createModel(array $payload, bool $stream = true): Iterator|StreamIterator|CreateModelResponse;

    /**
     * List all locally available models.
     *
     * @return ListLocalModelsResponse
     */
    public function listModels(): ListLocalModelsResponse;

    /**
     * Show detailed information about a model.
     *
     * @param string $model Model name.
     * @param bool $verbose Whether to include verbose information.
     * @return ShowModelInfoResponse
     */
    public function showModel(string $model, bool $verbose = false): ShowModelInfoResponse;

    /**
     * Copy a model to a new name.
     *
     * @param string $source Source model name.
     * @param string $destination Destination model name.
     * @return CopyModelResponse
     */
    public function copy(string $source, string $destination): CopyModelResponse;

    /**
     * Delete a model.
     *
     * @param string $model Model name.
     * @return DeleteModelResponse
     */
    public function delete(string $model): DeleteModelResponse;

    /**
     * Pull a model from a remote repository.
     *
     * @param string $model Model name.
     * @param bool $insecure Allow insecure connection.
     * @param bool $stream Stream the pull response.
     * @return Iterator|PullModelResponse
     */
    public function pull(string $model, bool $insecure = false, bool $stream = true);

    /**
     * Push a local model to a remote repository.
     *
     * @param string $model Model name.
     * @param bool $insecure Allow insecure connection.
     * @param bool $stream Stream the push response.
     * @return Iterator|PushModelResponse
     */
    public function push(string $model, bool $insecure = false, bool $stream = true);

    /**
     * Embed input text using a model.
     *
     * @param array $payload Embedding request payload.
     * @return EmbedResponse
     */
    public function embed(array $payload): EmbedResponse;

    /**
     * List running models.
     *
     * @return RunningModelsResponse
     */
    public function runningModels(): RunningModelsResponse;

    /**
     * Get the version of the Ollama API.
     *
     * @return VersionResponse
     */
    public function version(): VersionResponse;
}
