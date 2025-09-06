<?php

namespace NativePlatform\Routes\Controllers;

use NativePlatform\SubContainer\Auth\AuthManager;
use PlatformBridge\BridgeConfig;
use NativePlatform\Db\EntityManager;
use NativePlatform\Templater\Engine as TemplateEngine;
use NativePlatform\Routes\Controller;
use NativePlatform\SubContainer\Style\UiInterface;
use NativePlatform\Scopes\RenderScope;
use NativePlatform\Scopes\StreamedRenderScope;

use NativePlatform\Adapters\Ollama\ClientInterface as OllamaClientInterface;
use NativePlatform\Adapters\LLamacpp\ClientInterface as LLamacppClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        if ($request->server->get('REQUEST_METHOD') === 'GET')
        {
            $template = $templater->renderFromFile('index.tpl', [
                'app' => [
                    'config' => $config,
                    'translator' => $translator,
                    'jsTranslatorPhraseList' => json_encode($this->getJsTranslationList($translator))
                ]
            ]);

            return $renderer->finalRender('html', $template);
        }
        else if ($request->server->get('REQUEST_METHOD') === 'POST')
        {
        }

        return new JsonResponse(['error' => 'Route not found'], 404);
    }

    public function stream()
    {
        /** @var Request $request */
        $request = $this->container->get('app:request');

        /** @var StreamedRenderScope $streamedRenderer */
        $streamedRenderer = $this->container->get('scope:streamed_renderer');

        /** @var LLamacppClientInterface|OllamaClientInterface $llmAdapter */
        $llmAdapter = $this->container->get('app:llm.adapter_manager')->get();

        $streamedRenderer->set(function () use ($llmAdapter, $request): void
        {
            $llmAdapter->chat([
                'model' => 'gemma3:1b',
                'messages' => [
                    ['role' => 'system', 'content' => 'Answer in turkish'],
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
