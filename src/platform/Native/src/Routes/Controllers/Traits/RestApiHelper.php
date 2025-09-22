<?php

namespace NativePlatform\Routes\Controllers\Traits;

trait RestApiHelper
{
    /**
     * Standard OpenAI style error response
     *
     * @param string $message
     * @param string $type
     * @param string $param
     * @param string $code
     * @return array
     */
    protected function responseError(string $message, string $type = 'invalid_request_error', ?string $param = null, ?string $code): array
    {
        return [
            'error' => [
                'message' => $message,
                'type' => $type,
                'param' => $param,
                'code' => $code,
            ]
        ];
    }

    /**
     * Validate json body
     *
     * @param array $body
     * @param string $endpointType
     * @return void
     */
    protected function validateBody(array $body, string $endpointType = 'chat')
    {
        if (!is_array($body))
        {
            return $this->renderer()->finalRender(
                'json',
                $this->responseError(
                    "Invalid JSON body",
                    'invalid_request_error',
                    null,
                    'invalid_json'
                ),
                400
            );
        }

        if ($endpointType === 'chat')
        {
            if (empty($body['model']) || empty($body['messages']))
            {
                return $this->renderer()->finalRender('json', $this->responseError(
                    "Missing required parameter(s): model, messages",
                    'invalid_request_error',
                    null,
                    'missing_fields'
                ), 400);
            }
        }

        if ($endpointType === 'completion')
        {
            if (empty($body['model']) || empty($body['prompt']))
            {
                return $this->renderer()->finalRender('json', $this->responseError(
                    "Missing required parameter(s): model, prompt",
                    'invalid_request_error',
                    null,
                    'missing_fields'
                ), 400);
            }
        }

        if ($endpointType === 'embed')
        {
            if (empty($body['model']) || empty($body['input']))
            {
                return $this->renderer()->finalRender(
                    'json',
                    $this->responseError(
                        "Missing required parameter(s): model, input",
                        'invalid_request_error',
                        null,
                        'missing_fields'
                    ),
                    400
                );
            }
        }
    }

    /**
     * Validate Bearer token from "Authorization" header.
     *
     * @param string $expectedKey The valid API key (e.g. from config/env/db)
     * @return array|null Null if valid, or error array if invalid
     */
    protected function validateBearer(string $expectedKey): ?array
    {
        $authHeader = $this->request()->headers->get('Authorization');

        if (!$authHeader)
        {
            return $this->responseError(
                'You must provide an Authorization Bearer token',
                'invalid_request_error',
                'Authorization',
                'missing_token'
            );
        }

        // Match "Bearer xxx"
        if (!preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches))
        {
            return $this->responseError(
                'Invalid Authorization header format. Use Authorization: Bearer {token}',
                'invalid_request_error',
                'Authorization',
                'invalid_header'
            );
        }

        $providedKey = trim($matches[1]);

        if ($providedKey !== $expectedKey)
        {
            return $this->responseError(
                'Invalid API key',
                'invalid_api_key',
                'Authorization',
                'invalid_token'
            );
        }

        return null;
    }
}
