<?php

declare(strict_types=1);

namespace NativePlatform\Adapters\Ollama;

use NativePlatform\Adapters\AdapterClient;
use NativePlatform\Adapters\Ollama\StreamIterator;
use NativePlatform\Adapters\Ollama\TokenStreamReader;
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

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Concrete implementation of {@see ClientInterface} using Guzzle.
 */
final class Client extends AdapterClient implements ClientInterface
{
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
        catch (RequestException $e)
        {
            throw new \RuntimeException(
                'Ollama request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Generate a text completion from the model.
     *
     * @param array $payload The API payload, including model and prompt
     * @param bool $stream Whether to stream the response
     * @param callable(string): void|null $onToken Optional callback for real-time token streaming
     * @return StreamIterator|TokenStreamReader|GenerateCompletionResponse|null
     */
    public function generate(array $payload, bool $stream = true, ?callable $onToken = null): TokenStreamReader|StreamIterator|GenerateCompletionResponse|null
    {
        $options = ['json' => $payload];

        if ($stream && $onToken !== null)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/generate', $options);

            $tokenReader = new TokenStreamReader($resp, $onToken);
            $tokenReader->start();

            return null;
        }

        if ($stream)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/generate', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/api/generate', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return GenerateCompletionResponse::fromArray($data);
    }

    /**
     * Interact with the model in a chat-style interface.
     *
     * @param array $payload Chat payload including model and messages
     * @param bool $stream Whether to stream the response
     * @param callable(string): void|null $onToken Optional callback for token streaming
     * @return StreamIterator|TokenStreamReader|GenerateChatResponse|null
     */
    public function chat(array $payload, bool $stream = true, ?callable $onToken = null): TokenStreamReader|StreamIterator|GenerateChatResponse|null
    {
        $options = ['json' => $payload];

        if ($stream && $onToken !== null)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/chat', $options);

            $tokenReader = new TokenStreamReader($resp, $onToken);
            $tokenReader->start();

            return null;
        }

        if ($stream)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/chat', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/api/chat', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return GenerateChatResponse::fromArray($data);
    }

    /**
     * Create a new model based on an existing one.
     *
     * @param array $payload Model creation payload
     * @param bool $stream Whether to stream the response
     * @return StreamIterator|CreateModelResponse
     */
    public function createModel(array $payload, bool $stream = true): StreamIterator|CreateModelResponse
    {
        $options = ['json' => $payload];
        if ($stream)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/create', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/api/create', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return CreateModelResponse::fromArray($data);
    }

    /**
     * List all locally available models.
     *
     * @return ListLocalModelsResponse
     */
    public function listModels(): ListLocalModelsResponse
    {
        $resp = $this->request('GET', '/api/tags');
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return ListLocalModelsResponse::fromArray($data);
    }

    /**
     * Show information about a specific model.
     *
     * @param string $model Model name
     * @param bool $verbose Return verbose details
     * @return ShowModelInfoResponse
     */
    public function showModel(string $model, bool $verbose = false): ShowModelInfoResponse
    {
        $options = ['json' => ['model' => $model, 'verbose' => $verbose]];
        $resp = $this->request('POST', '/api/show', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return ShowModelInfoResponse::fromArray($data);
    }

    /**
     * Copy an existing model to a new model name.
     *
     * @param string $source Source model name
     * @param string $destination Destination model name
     * @return CopyModelResponse
     */
    public function copy(string $source, string $destination): CopyModelResponse
    {
        $options = ['json' => ['source' => $source, 'destination' => $destination]];
        $resp = $this->request('POST', '/api/copy', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return CopyModelResponse::fromArray($data);
    }

    /**
     * Delete a model.
     *
     * @param string $model Model name to delete
     * @return DeleteModelResponse
     */
    public function delete(string $model): DeleteModelResponse
    {
        $options = ['json' => ['model' => $model]];
        $resp = $this->request('DELETE', '/api/delete', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return DeleteModelResponse::fromArray($data);
    }

    /**
     * Pull a model from remote storage.
     *
     * @param string $model Model name
     * @param bool $insecure Whether to allow insecure connections
     * @param bool $stream Stream the response
     * @return StreamIterator|PullModelResponse
     */
    public function pull(string $model, bool $insecure = false, bool $stream = true)
    {
        $options = ['json' => ['model' => $model, 'insecure' => $insecure]];
        if ($stream)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/pull', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/api/pull', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return PullModelResponse::fromArray($data);
    }

    /**
     * Push a model to remote storage.
     *
     * @param string $model Model name
     * @param bool $insecure Allow insecure connections
     * @param bool $stream Stream the response
     * @return StreamIterator|PushModelResponse
     */
    public function push(string $model, bool $insecure = false, bool $stream = true)
    {
        $options = ['json' => ['model' => $model, 'insecure' => $insecure]];
        if ($stream)
        {
            $options['stream'] = true;
            $resp = $this->request('POST', '/api/push', $options);
            return new StreamIterator($resp);
        }

        $resp = $this->request('POST', '/api/push', $options);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return PushModelResponse::fromArray($data);
    }

    /**
     * Generate embeddings for input text using a model.
     *
     * @param array $payload Embedding payload (model, input text)
     * @return EmbedResponse
     */
    public function embed(array $payload): EmbedResponse
    {
        $resp = $this->request('POST', '/api/embed', ['json' => $payload]);
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return EmbedResponse::fromArray($data);
    }

    /**
     * List all currently running models.
     *
     * @return RunningModelsResponse
     */
    public function runningModels(): RunningModelsResponse
    {
        $resp = $this->request('GET', '/api/running');
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return RunningModelsResponse::fromArray($data);
    }

    /**
     * Return the current Ollama version.
     *
     * @return VersionResponse
     */
    public function version(): VersionResponse
    {
        $resp = $this->request('GET', '/api/version');
        $data = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return VersionResponse::fromArray($data);
    }
}
