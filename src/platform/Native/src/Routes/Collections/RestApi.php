<?php

declare(strict_types=1);

use PlatformBridge\RouteCollection;
use NativePlatform\Routes\Controllers\RestApi\{
    Models,
    Completions,
    Embeddings
};

return function ()
{
    /**
     * API v1 - OpenAI Compatible Endpoints
     *
     * Authentication:
     *   - Every request must include an Authorization header with a valid API key.
     *   - Example: Authorization: Bearer YOUR_API_KEY
     *
     * Request / Response format:
     *   - All requests and responses strictly follow the OpenAI REST API schema.
     *   - Standard endpoints: /models, /completions, /chat/completions, /embeddings, etc.
     *
     * Streaming:
     *   - Endpoints that support completions (e.g., /chat/completions, /completions) accept a `stream` parameter.
     *   - "stream": true
     *       → The response will be sent as a server-sent event (SSE) stream of JSON objects, each line starting with "data:".
     *       → Final message is always: `data: [DONE]`
     *   - "stream": false (default)
     *       → The response will be a single JSON object with completion content.
     *
     * Example Request (Chat Completion, non-streamed):
     *   POST /api/v1/chat/completions
     *   Headers:
     *     Content-Type: application/json
     *     Authorization: Bearer sk-xxxxxx
     *
     *   Body:
     *   {
     *     "model": "ollama:llama2:7b",
     *     "messages": [
     *       {"role": "user", "content": "Hello world"}
     *     ],
     *     "temperature": 0.7,
     *     "stream": false
     *   }
     *
     * Example Request (Chat Completion, streamed):
     *   POST /api/v1/chat/completions
     *   Headers:
     *     Content-Type: application/json
     *     Authorization: Bearer sk-xxxxxx
     *
     *   Body:
     *   {
     *     "model": "gpt:gpt-3.5-turbo",
     *     "messages": [
     *       {"role": "user", "content": "Hello, who are you?"}
     *     ],
     *     "stream": true
     *   }
     *
     * Example Streamed Response:
     *   data: {"id":"chatcmpl-123","object":"chat.completion.chunk","choices":[{"index":0,"delta":{"content":"Hello"}}]}
     *   data: {"id":"chatcmpl-123","object":"chat.completion.chunk","choices":[{"index":0,"delta":{"content":" there"}}]}
     *   data: [DONE]
     */
    RouteCollection::prefix('api/v1', function ()
    {
        /**
         * GET /v1/models
         *
         * Request:
         *   GET /v1/models
         *
         * Response:
         * {
         *   "object": "list",
         *   "data": [
         *     { "id": "rig:aio-1-pro", "object": "model", "owned_by": "synui" },
         *     { "id": "gpt:gpt-3.5-turbo", "object": "model", "owned_by": "openai" },
         *     { "id": "gpt:gpt-4", "object": "model", "owned_by": "openai" },
         *     { "id": "ollama:gemma2:2b", "object": "model", "owned_by": "ollama" },
         *     { "id": "ollama:llama2:7b", "object": "model", "owned_by": "ollama" },
         *     { "id": "llamacpp:gemma2:1b", "object": "model", "owned_by": "llamacpp" },
         *     { "id": "llamacpp:mistral:7b", "object": "model", "owned_by": "llamacpp" }
         *   ]
         * }
         */
        RouteCollection::register(
            'app.rest-api:models',
            ['GET'],
            '/models',
            [Models::class, 'models']
        );

        /**
         * GET /v1/models/{model}
         *
         * Request:
         *   GET /v1/models/ollama:gemma2:2b
         *
         * Response:
         * {
         *   "id": "ollama:gemma2:2b",
         *   "object": "model",
         *   "owned_by": "ollama"
         * }
         */
        RouteCollection::register(
            'app.rest-api:model_info',
            ['GET'],
            '/models/{model}',
            [Models::class, 'info']
        );

        /**
         * POST /v1/completions
         *
         * Request:
         * {
         *   "model": "rig:aio-1-pro",
         *   "prompt": "Hello world",
         *   "max_tokens": 10
         * }
         *
         * Response:
         * {
         *   "id": "cmpl-123",
         *   "object": "text_completion",
         *   "created": 1670000000,
         *   "model": "rig:aio-1-pro",
         *   "choices": [
         *     { "text": " Hello!", "index": 0, "finish_reason": "stop" }
         *   ]
         * }
         */
        RouteCollection::register(
            'app.rest-api:completions',
            ['POST'],
            '/completions',
            [Completions::class, 'completions']
        );

        /**
         * POST /v1/chat/completions
         *
         * Request:
         * {
         *   "model": "rig:aio-1-pro",
         *   "messages": [
         *     {"role": "user", "content": "Hello!"}
         *   ],
         *   "temperature": 0.7,
         *   "max_tokens": 50
         * }
         *
         * Response:
         * {
         *   "id": "chatcmpl-123",
         *   "object": "chat.completion",
         *   "created": 1670000000,
         *   "model": "rig:aio-1-pro",
         *   "choices": [
         *     {
         *       "index": 0,
         *       "message": {"role": "assistant", "content": "Hi, I can help you."},
         *       "finish_reason": "stop"
         *     }
         *   ]
         * }
         */
        RouteCollection::register(
            'app.rest-api:chat-completions',
            ['POST'],
            '/chat/completions',
            [Completions::class, 'chat']
        );

        /**
         * POST /v1/embeddings
         *
         * Request:
         * {
         *   "model": "llamacpp:gemma2:1b",
         *   "input": "Hello world"
         * }
         *
         * Response:
         * {
         *   "object": "list",
         *   "data": [
         *     {
         *       "object": "embedding",
         *       "index": 0,
         *       "embedding": [0.012, -0.034, 0.056, ...]
         *     }
         *   ],
         *   "model": "llamacpp:gemma2:1b",
         *   "usage": { "prompt_tokens": 2, "total_tokens": 2 }
         * }
         */
        RouteCollection::register(
            'app.rest-api:embeddings',
            ['POST'],
            '/embeddings',
            [Embeddings::class, 'embeddings']
        );
    });
};
