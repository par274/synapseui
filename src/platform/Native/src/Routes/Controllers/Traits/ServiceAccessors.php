<?php

namespace NativePlatform\Routes\Controllers\Traits;

use NativePlatform\SubContainer\ServiceContainer;
use PlatformBridge\BridgeConfig;
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

trait ServiceAccessors
{
    protected ServiceContainer $container;

    /**
     * Get application configuration service.
     *
     * @return BridgeConfig
     */
    public function config(): BridgeConfig
    {
        return $this->container->get('app:config');
    }

    /**
     * Get database entity manager service.
     *
     * @return EntityManager
     */
    public function em(): EntityManager
    {
        return $this->container->get('db:em');
    }

    /**
     * Get HTTP request service.
     *
     * @return Request
     */
    public function request(): Request
    {
        return $this->container->get('app:request');
    }

    /**
     * Get HTTP response service.
     *
     * @return Response
     */
    public function response(): Response
    {
        return $this->container->get('app:response');
    }

    /**
     * Get template engine service.
     *
     * @return TemplateEngine
     */
    public function templater(): TemplateEngine
    {
        return $this->container->get('app:templater');
    }

    /**
     * Get authentication manager service.
     *
     * @return AuthManager
     */
    public function auth(): AuthManager
    {
        return $this->container->get('app:auth');
    }

    /**
     * Get routing parameters.
     *
     * @return array
     */
    public function params(): array
    {
        return $this->container->get('routing:params');
    }

    /**
     * Get templater UI service.
     *
     * @return UiInterface
     */
    public function ui(): UiInterface
    {
        return $this->container->get('templater:ui')->driver();
    }

    /**
     * Get renderer scope service.
     *
     * @return RenderScope
     */
    public function renderer(): RenderScope
    {
        return $this->container->get('scope:renderer');
    }

    /**
     * Get translations service.
     *
     * @return Translator
     */
    public function translator(): Translator
    {
        return $this->container->get('app:translations');
    }

    /**
     * Get streamed renderer scope service.
     *
     * @return StreamedRenderScope
     */
    public function streamedRenderer(): StreamedRenderScope
    {
        return $this->container->get('scope:streamed_renderer');
    }

    /**
     * Get LLM adapter manager service.
     *
     * @return AdapterManager
     */
    public function adapterManager(): AdapterManager
    {
        return $this->container->get('app:llm.adapter_manager');
    }
    
    /**
     * Get LLM adapter interface.
     *
     * @return LLamacppClientInterface|OllamaClientInterface
     */
    public function getLLMAdapter(): LLamacppClientInterface|OllamaClientInterface
    {
        return (function ()
        {
            $manager = $this->adapterManager();
            $adapter = $manager->get();

            if ($manager->is('llamacpp') && $this->config()->getEnv() === 'dev')
            {
                $adapter->useCpu();
            }

            if ($manager->isGpuUtilize())
            {
                $adapter->useForceGPU();
            }

            return $adapter;
        })();
    }
}
