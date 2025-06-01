<?php

namespace NativePlatform\Adapters\Ollama;

use GuzzleHttp\Exception\GuzzleException;

use NativePlatform\Adapters\AdapterClient;
use NativePlatform\Adapters\AdapterInterface;
use NativePlatform\Adapters\Ollama\Response\Chat\ChatResponse;
use NativePlatform\Adapters\Ollama\Response\Completion\CompletionResponse;
use NativePlatform\Adapters\Ollama\Response\Model\PullResponse;
use NativePlatform\Adapters\Ollama\Response\Model\InfoResponse;
use NativePlatform\Adapters\Ollama\Response\Model\DeleteResponse;
use NativePlatform\Adapters\Ollama\Response\Model\ListTagsResponse;
use NativePlatform\Adapters\Ollama\Response\VersionResponse;
use NativePlatform\Adapters\Ollama\Response\Model\PsResponse;

class OllamaAdapterClient extends AdapterClient implements AdapterInterface
{
    public function test(): bool
    {
        try
        {
            $response = $this->client->request('GET', "/");
            $content = $response->getBody()->getContents();

            return $content === 'Ollama is running';
        }
        catch (GuzzleException $e)
        {
            return false;
            exit(0);
        }
    }

    public function chat(array $data = []): ChatResponse
    {
        $options = [
            'json' => [
                'model' => $data['model'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => (isset($data['system']) ? $data['system'] : 'You are an AI assistant')
                    ],
                    [
                        'role' => 'user',
                        'content' => $data['prompt'],
                    ]
                ],
                'stream' => $data['stream']
            ]
        ];
        $response = $this->client->request('POST', "/api/chat", $options);

        return new ChatResponse($response);
    }

    public function completion(array $data = []): CompletionResponse
    {
        $options = [
            'json' => [
                'model' => $data['model'],
                'prompt' => $data['prompt'],
                'stream' => $data['stream']
            ]
        ];
        $response = $this->client->request('POST', "/api/generate", $options);

        return new CompletionResponse($response);
    }

    public function pull(string $model, bool $stream = true): PullResponse
    {
        $options = [
            'json' => [
                'model' => $model,
                'stream' => $stream ? 'true' : 'false'
            ]
        ];
        $response = $this->client->request('POST', "/api/pull", $options);

        return new PullResponse($response);
    }

    public function modelInfo(string $model, bool $verbose = false): InfoResponse
    {
        $options = [
            'json' => [
                'model' => $model,
                'verbose' => $verbose ? 'true' : 'false'
            ]
        ];
        $response = $this->client->request('POST', "/api/show", $options);

        return new InfoResponse($response);
    }

    public function delete(string $model): DeleteResponse
    {
        $options = [
            'json' => [
                'model' => $model
            ]
        ];
        $response = $this->client->request('DELETE', "/api/delete", $options);

        return new DeleteResponse($response);
    }

    public function listTags(): ListTagsResponse
    {
        $response = $this->client->request('GET', "/api/tags");

        return new ListTagsResponse($response);
    }

    public function ps(): PsResponse
    {
        $response = $this->client->request('GET', "/api/ps");

        return new PsResponse($response);
    }

    public function version(): VersionResponse
    {
        $response = $this->client->request('GET', "/api/version");

        return new VersionResponse($response);
    }
}
