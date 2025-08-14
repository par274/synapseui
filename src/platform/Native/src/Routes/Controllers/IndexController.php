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

        /** @var OllamaClientInterface $llmAdapter */
        $llmAdapter = $this->container->get('app:llm.adapter_manager')->get();
/**
 * === Ollama API Usage Examples ===
 */

/**
 * ---------------------------
 * 1. generate()
 * ---------------------------
 *
 * // Payload
 * $payload = [
 *     'model' => 'llama3.2',
 *     'prompt' => 'Why is the sky blue?'
 * ];
 *
 * // Streaming
 * foreach ($llmAdapter->generate($payload, true) as $chunk) {
 *     echo "Response chunk: ", json_encode($chunk), PHP_EOL;
 * }
 *
 * // Token stream
 * $llmAdapter->generate($payload, true, function(string $token) {
 *     echo $token;
 * });
 *
 * // Non-streaming
 * $response = $llmAdapter->generate($payload, false);
 * echo "Full response: ", $response->text, PHP_EOL;
 */

/**
 * ---------------------------
 * 2. chat()
 * ---------------------------
 *
 * $payload = [
 *     'model' => 'llama3.2',
 *     'messages' => [
 *         ['role' => 'user', 'content' => 'why is the sky blue?']
 *     ]
 * ];
 *
 * // Streaming
 * foreach ($llmAdapter->chat($payload, true) as $chunk) {
 *     echo "Response chunk: ", json_encode($chunk), PHP_EOL;
 * }
 *
 * // Token stream
 * $llmAdapter->chat($payload, true, function(string $token) {
 *     echo $token;
 * });
 *
 * // Non-streaming
 * $response = $llmAdapter->chat($payload, false);
 * echo "Full chat response: ", $response->messages[0]['content'], PHP_EOL;
 */

/**
 * ---------------------------
 * 3. create()
 * ---------------------------
 *
 * $payload = [
 *     'model' => 'mario',
 *     'from' => 'llama3.2',
 *     'system' => 'You are Mario from Super Mario Bros.'
 * ];
 * $response = $llmAdapter->create($payload);
 * echo "Created model: ", $response['name'], PHP_EOL;
 */

/**
 * ---------------------------
 * 4. tags()
 * ---------------------------
 *
 * $tags = $llmAdapter->tags();
 * foreach ($tags as $tag) {
 *     echo "Tag: ", $tag, PHP_EOL;
 * }
 */

/**
 * ---------------------------
 * 5. show()
 * ---------------------------
 *
 * $payload = ['model' => 'llava'];
 * $info = $llmAdapter->show($payload);
 * echo "Model info: ", json_encode($info), PHP_EOL;
 */

/**
 * ---------------------------
 * 6. copy()
 * ---------------------------
 *
 * $payload = ['source' => 'llama3.2', 'destination' => 'llama3-backup'];
 * $response = $llmAdapter->copy($payload);
 * echo "Copy response: ", json_encode($response), PHP_EOL;
 */

/**
 * ---------------------------
 * 7. pull()
 * ---------------------------
 *
 * $payload = ['model' => 'llama3.2'];
 * $response = $llmAdapter->pull($payload);
 * echo "Pull response: ", json_encode($response), PHP_EOL;
 */

/**
 * ---------------------------
 * 8. push()
 * ---------------------------
 *
 * $payload = ['model' => 'mattw/pygmalion:latest'];
 * $response = $llmAdapter->push($payload);
 * echo "Push response: ", json_encode($response), PHP_EOL;
 */

/**
 * ---------------------------
 * 9. delete()
 * ---------------------------
 *
 * $payload = ['model' => 'llama3:13b'];
 * $response = $llmAdapter->delete($payload);
 * echo "Delete response: ", json_encode($response), PHP_EOL;
 */

/**
 * ---------------------------
 * 10. embed()
 * ---------------------------
 *
 * $payload = ['model' => 'all-minilm', 'input' => 'Why is the sky blue?'];
 * $embedding = $llmAdapter->embed($payload);
 * echo "Embedding: ", json_encode($embedding), PHP_EOL;
 */

/**
 * ---------------------------
 * 11. ps()
 * ---------------------------
 *
 * $processes = $llmAdapter->ps();
 * foreach ($processes as $proc) {
 *     echo "Process: ", json_encode($proc), PHP_EOL;
 * }
 */

/**
 * ---------------------------
 * 12. version()
 * ---------------------------
 *
 * $version = $llmAdapter->version();
 * echo "Ollama version: ", $version, PHP_EOL;
 */

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
