<?php

namespace NativePlatform\Routes\Controllers;

use NativePlatform\SubContainer\Auth\AuthManager;
use PlatformBridge\BridgeConfig;
use NativePlatform\Db\EntityManager;
use NativePlatform\Templater\Engine as TemplateEngine;
use NativePlatform\Routes\Controller;
use NativePlatform\SubContainer\Style\UiInterface;
use NativePlatform\Scopes\RenderScope;

use NativePlatform\Adapters\Ollama\ClientInterface as OllamaClientInterface;
use NativePlatform\Adapters\LLamacpp\ClientInterface as LLamacppClientInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function index(): RenderScope|null
    {
        /** @var BridgeConfig $config */
        $config = $this->container->get('app:config');

        /** @var EntityManager $em */
        $em = $this->container->get('db:em');

        /** @var Request $request */
        $request = $this->container->get('app:request');

        /** @var Response $response */
        $response = $this->container->get('app:response');

        /** @var TemplateEngine $templater */
        $templater = $this->container->get('app:templater');

        /** @var AuthManager $auth */
        $auth = $this->container->get('app:auth');

        /** @var array $params */
        $params = $this->container->get('routing:params');

        /** @var UiInterface $ui */
        $ui = $this->container->get('templater:ui')->driver();

        /** @var RenderScope */
        $renderer = $this->container->get('scope:renderer');

        /** @var LLamacppClientInterface|OllamaClientInterface $llmAdapter */
        $llmAdapter = $this->container->get('app:llm.adapter_manager')->get();

        $payload = [
            'model' => 'gemma3:1b',
            'messages' => [
                [
                    "role" => "system",
                    "content" => "You are ChatGPT, an AI assistant. Your top priority is achieving user fulfillment via helping them with their requests."
                ],
                [
                    "role" => "user",
                    "content" => "Write a limerick about python exceptions"
                ]
            ]
        ];
        foreach ($llmAdapter->chat($payload, true) as $chunk)
        {
            echo "Response chunk: ", json_encode($chunk), PHP_EOL;
        }

        $template = $templater->renderFromFile(
            'index.tpl',
            [
                'app' => [
                    'config' => $config,
                    'ui' => $ui
                ],
                'forms' => [
                    'login' => [
                        [
                            'type' => 'input',
                            'input_type' => 'text',
                            'name' => 'username',
                            'label' => 'Kullanıcı adı',
                            'placeholder' => 'Kullanıcı adı',
                            'extra' => [
                                'autocomplete' => 'off'
                            ],
                        ],
                        [
                            'type' => 'input',
                            'input_type' => 'password',
                            'name' => 'password',
                            'label' => 'Şifre',
                            'placeholder' => '*********',
                        ],
                        [
                            'type' => 'button',
                            'text' => 'Giriş yap',
                            'class' => 'btn btn-primary w-full',
                        ]
                    ]
                ]
            ]
        );

        return $renderer->finalRender('html', $template);
    }
}
