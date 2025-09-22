<?php

namespace NativePlatform\Routes\Controllers\RestApi;

use NativePlatform\Routes\Controller;
use NativePlatform\Routes\Controllers\Traits\{
    ServiceAccessors,
    RestApiHelper
};

class Completions extends Controller
{
    use ServiceAccessors, RestApiHelper;

    public function chat()
    {
        if ($this->request()->isMethod('POST'))
        {
            $body = json_decode($this->request()->getContent(), true);
            $this->validateBody($body);

            $useStream = !empty($body['stream']) && $body['stream'] === true;
            if ($useStream)
            {
                $streamedRenderer = $this->streamedRenderer();
                $streamedRenderer->set(function () use ($body): void
                {
                    $this->getLLMAdapter()->chat(
                        [
                            'model' => $body['model'],
                            'messages' => $body['messages']
                        ],
                        true,
                        function (string $token)
                        {
                            echo $token;
                        }
                    );
                });
                $streamedRenderer->sendBuffer();
            }
            else
            {
                $output = $this->getLLMAdapter()->chat(
                    [
                        'model' => $body['model'],
                        'messages' => $body['messages']
                    ],
                    false
                )->json();

                return $this->renderer()->finalRender('json', $output);
            }
        }
    }

    public function completions()
    {
        if ($this->request()->isMethod('POST'))
        {
            $body = json_decode($this->request()->getContent(), true);
            $this->validateBody($body, 'completion');

            $useStream = !empty($body['stream']) && $body['stream'] === true;
            if ($useStream)
            {
                $streamedRenderer = $this->streamedRenderer();
                $streamedRenderer->set(function () use ($body): void
                {
                    $this->getLLMAdapter()->completion(
                        [
                            'model' => $body['model'],
                            'prompt' => $body['prompt'],
                            'max_tokens' => $body['max_tokens'] ?? 128,
                            'temperature' => $body['temperature'] ?? 0.7,
                        ],
                        true,
                        function (string $token)
                        {
                            echo $token;
                        }
                    );
                });
                $streamedRenderer->sendBuffer();
            }
            else
            {
                $output = $this->getLLMAdapter()->completion(
                    [
                        'model' => $body['model'],
                        'prompt' => $body['prompt'],
                        'max_tokens' => $body['max_tokens'] ?? 128,
                        'temperature' => $body['temperature'] ?? 0.7,
                    ],
                    false
                )->json();

                return $this->renderer()->finalRender('json', $output);
            }
        }
    }
}
