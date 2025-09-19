<?php

namespace NativePlatform\Routes\Controllers;

use PlatformBridge\BridgeConfig;
use NativePlatform\Routes\Controller;
use NativePlatform\Db\EntityManager;
use NativePlatform\Templater\Engine as TemplateEngine;
use NativePlatform\SubContainer\Auth\AuthManager;
use NativePlatform\SubContainer\Style\UiInterface;
use NativePlatform\Scopes\{
    RenderScope,
    StreamedRenderScope
};
use NativePlatform\Adapters\{
    AdapterManager,
    Ollama\ClientInterface as OllamaClientInterface,
    LLamacpp\ClientInterface as LLamacppClientInterface
};

use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Translation\Translator;

class IndexController extends Controller
{
    public function index()
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

        /** @var Translator $translator */
        $translator = $this->container->get('app:translations');

        if ($request->isMethod('GET'))
        {
            $template = $templater->renderFromFile('index.tplx', [
                'app' => [
                    'config' => $config,
                    'translator' => $translator,
                    'jsTranslatorPhraseList' => json_encode($this->getJsTranslationList($translator))
                ]
            ]);

            return $renderer->finalRender('html', $template);
        }
        else if ($request->isMethod('POST'))
        {
        }
    }

    public function stream()
    {
        /** @var BridgeConfig $config */
        $config = $this->container->get('app:config');

        /** @var Request $request */
        $request = $this->container->get('app:request');

        if ($request->isMethod('POST'))
        {
            /** @var RenderScope */
            $renderer = $this->container->get('scope:renderer');

            $data = json_decode($request->getContent(), true);

            return $renderer->finalRender('json', [
                'chat_id' => $data['chat_id'],
                'message' => $data['message']
            ]);
        }

        if ($request->isMethod('GET') && $request->query->has('stream'))
        {
            /** @var StreamedRenderScope $streamedRenderer */
            $streamedRenderer = $this->container->get('scope:streamed_renderer');

            /** @var LLamacppClientInterface|OllamaClientInterface $llmAdapter */
            $llmAdapter = (function () use ($config)
            {
                /** @var AdapterManager $manager */
                $manager = $this->container->get('app:llm.adapter_manager');

                /** @var LLamacppClientInterface|OllamaClientInterface $adapter */
                $adapter = $manager->get();

                if ($manager->is('llamacpp') && $config->getEnv() === 'dev')
                {
                    $adapter->useCpu();
                }

                if ($manager->isGpuUtilize())
                {
                    $adapter->useForceGPU();
                }

                return $adapter;
            })();

            $streamedRenderer->set(function () use ($llmAdapter, $request): void
            {
                $llmAdapter->chat([
                    'model' => 'gemma3:1b', # for llama-swap
                    'messages' => [
                        ['role' => 'system', 'content' => 'Answer in english'],
                        ['role' => 'user', 'content' => $request->query->get('message', 'hi')]
                    ]
                ], true, function (string $token)
                {
                    echo $token;
                });
            });

            $streamedRenderer->sendBuffer();
        }
    }
}
