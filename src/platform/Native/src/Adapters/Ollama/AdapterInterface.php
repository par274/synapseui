<?php

namespace NativePlatform\Adapters\Ollama;

use NativePlatform\Adapters\Ollama\Response\Chat\ChatResponse;
use NativePlatform\Adapters\Ollama\Response\Completion\CompletionResponse;
use NativePlatform\Adapters\Ollama\Response\Model\PullResponse;
use NativePlatform\Adapters\Ollama\Response\Model\InfoResponse;
use NativePlatform\Adapters\Ollama\Response\Model\DeleteResponse;
use NativePlatform\Adapters\Ollama\Response\Model\ListTagsResponse;
use NativePlatform\Adapters\Ollama\Response\VersionResponse;
use NativePlatform\Adapters\Ollama\Response\Model\PsResponse;

interface AdapterInterface
{
    public function test(): bool;
    public function chat(array $data = []): ChatResponse;
    public function completion(array $data = []): CompletionResponse;
    public function pull(string $model, bool $stream = true): PullResponse;
    public function modelInfo(string $model, bool $verbose = false): InfoResponse;
    public function delete(string $model): DeleteResponse;
    public function listTags(): ListTagsResponse;
    public function version(): VersionResponse;
    public function ps(): PsResponse;
}
