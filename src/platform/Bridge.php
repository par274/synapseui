<?php

namespace PlatformBridge;

use PlatformBridge\BridgeConfig;
use NativePlatform\SubContainer\ServiceContainer;
use NativePlatform\SubContainer\SecurityConfig;
use NativePlatform\Db\EntityManager;
use NativePlatform\Templater\Engine as TemplateEngine;
use NativePlatform\SubContainer\Style\UiManager;
use NativePlatform\SubContainer\Style\DaisyUI;
use NativePlatform\SubContainer\Auth\AuthManager;
use NativePlatform\SubContainer\Security\CaptchaManager;
use NativePlatform\SubContainer\Security\GoogleRecaptchaValidator;
use NativePlatform\SubContainer\Security\CloudflareTurnstileValidator;
use NativePlatform\Adapters\AdapterManager as LLMAdapterManager;
use NativePlatform\Adapters\Ollama\Client as OllamaAdapterClient;
use NativePlatform\Adapters\LLamacpp\Client as LLamacppAdapterClient;
use NativePlatform\Scopes\RenderScope;
use NativePlatform\Exception\ExceptionManager;
use NativePlatform\Exception\Handler\LogHandler;
use PlatformBridge\Logging;
use NativePlatform\Exception\Handler\PlainTextHandler;
use NativePlatform\Exception\Handler\PrettyPageHandler;
use NativePlatform\Exception\Handler\JsonResponseHandler;
use NativePlatform\Exception\Handler\ProdExceptionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class Bridge
{
    protected Connection $db;
    protected BridgeConfig $config;
    protected EntityManager $em;
    protected Request $request;
    protected Response $response;
    protected TemplateEngine $templater;
    protected AuthManager $auth;
    protected Session $session;

    public ServiceContainer $container;

    public function __construct()
    {
        $this->initEnv();
        $this->initHttpFoundation();
        $this->initDatabase();
        $this->initTemplater();
        $this->initSession();
        $this->initContainer();
    }

    protected function initContainer(): void
    {
        $this->container = new ServiceContainer();

        // Basic services
        $this->container->set('app:config', $this->config);

        // Lazy services
        $this->container->set('app:request', fn() => $this->request);
        $this->container->set('app:response', fn() => $this->response);
        $this->container->set('app:session', fn() => $this->session);
        $this->container->set('db:em', fn() => $this->em);

        $this->container->set('templater:ui.daisy', fn() => new DaisyUI());
        $this->container->set('templater:ui', function (ServiceContainer $c)
        {
            return new UiManager(
                $c->get('app:config'),
                [
                    'daisyui' => $c->get('templater:ui.daisy'),
                ]
            );
        });
        $this->container->set('app:templater', function (ServiceContainer $c)
        {
            /** @var UiManager $manager */
            $manager = $c->get('templater:ui');
            $manager->use('daisyui');
            $this->templater->setUI(
                $manager->get()
            );

            return $this->templater;
        });

        $this->container->set('scope:renderer', function (ServiceContainer $c)
        {
            return new RenderScope(
                $c->get('app:response')
            );
        });

        $this->container->set('app:exception', function (ServiceContainer $c)
        {
            $manager = new ExceptionManager(
                $c->get('app:response'),
                $c->get('scope:renderer')
            );
            $manager->disableGlobalLog();

            // We can use default simple file logging, if you want can use Monolog eg.
            $logger = new Logging(INTERNAL_DIR . '/logs/errors.log');
            $logHandler = new LogHandler($logger);

            $manager->pushHandler($logHandler);
            //$manager->pushHandler(new PlainTextHandler());
            //$manager->pushHandler(new JsonResponseHandler());
            $manager->pushHandler(new PrettyPageHandler($c->get('app:templater')));
            $manager->pushHandler(new ProdExceptionHandler($c->get('app:templater')));

            // If has a multiple handler, you can try forcePushHandler() method. It's not affect on log handler.
            match ($c->get('app:config')->getEnv())
            {
                'dev' => $manager->forcePushHandler(PrettyPageHandler::class),
                'prod' => $manager->forcePushHandler(ProdExceptionHandler::class)
            };

            $manager->register();
        })()->bootstrap();

        $this->container->set('security:captcha.google', fn() => new GoogleRecaptchaValidator($this->config));
        $this->container->set('security:captcha.cloudflare', fn() => new CloudflareTurnstileValidator($this->config));
        $this->container->set('security:captcha', function (ServiceContainer $c)
        {
            return new CaptchaManager(
                $c->get('app:config'),
                $c->get('security:captcha.google'),
                $c->get('security:captcha.cloudflare')
            );
        });

        $this->container->set('app:auth', function (ServiceContainer $c)
        {
            return new AuthManager(
                $c->get('app:session'),
                $c->get('app:request'),
                $c->get('app:response'),
                $c->get('db:em')->user,
                $c->get('security:captcha')
            );
        });

        $this->container->set('llm:adapter.ollama', function (ServiceContainer $c)
        {
            /** @var BridgeConfig $config */
            $config = $c->get('app:config');
            return match ($config->getLLMAdapterMethod())
            {
                'client' => new OllamaAdapterClient(),
                default => new OllamaAdapterClient()
            };
        });
        $this->container->set('llm:adapter.llamacpp', function (ServiceContainer $c)
        {
            /** @var BridgeConfig $config */
            $config = $c->get('app:config');
            return match ($config->getLLMAdapterMethod())
            {
                'client' => new LLamacppAdapterClient(),
                default => new LLamacppAdapterClient()
            };
        });
        $this->container->set('app:llm.adapter_manager', function (ServiceContainer $c)
        {
            /** @var BridgeConfig $config */
            $config = $c->get('app:config');
            $manager = new LLMAdapterManager(
                $config,
                [
                    'ollama' => $c->get('llm:adapter.ollama'),
                    'llamacpp' => $c->get('llm:adapter.llamacpp')
                ]
            );
            $manager->use($config->getLLMAdapter());

            return $manager;
        });
    }

    protected function initEnv(): void
    {
        $this->config = new BridgeConfig($_ENV);

        SecurityConfig::setBridgeConfig($this->config);
    }

    protected function initDatabase(): void
    {
        $this->db = DriverManager::getConnection($this->config->getDatabaseParams());
        $this->em = new EntityManager($this->db);

        try
        {
            $this->em->test();
        }
        catch (Exception $e)
        {
            $this->response->setStatusCode(500);
            $this->response->setContent($e->getMessage());
            $this->response->send();
            exit(1);
        }
    }

    protected function initTemplater(): void
    {
        $this->templater = new TemplateEngine(
            WEB_PLATFORM_DIR . '/src/View',
            INTERNAL_DIR . '/template_cache'
        );
    }

    protected function initHttpFoundation(): void
    {
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
    }

    protected function initSession(): void
    {
        $storage = new NativeSessionStorage([
            'cookie_httponly' => true,
            'cookie_secure' => $this->request->isSecure(),
            'cookie_samesite' => 'Strict',
        ]);

        $this->session = new Session($storage);
        $this->session->start();
    }
}
