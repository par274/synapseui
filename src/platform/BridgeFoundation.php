<?php

namespace PlatformBridge;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FastRoute\Dispatcher;

class BridgeFoundation
{
    protected Bridge $bridge;

    public function __construct()
    {
        $this->bridge = new Bridge();
    }

    public function run(): void
    {
        /** @var Request $request */
        $request = $this->bridge->container->get('app:request');

        /** @var Response $response */
        $response = $this->bridge->container->get('app:response');

        /** @var Dispatcher $router */
        $router = $this->bridge->container->get('app:router');

        $httpMethod = $request->getMethod();
        $uri = $request->getPathInfo();

        $routeInfo = $router->dispatch($httpMethod, $uri);

        $status = $routeInfo[0];

        match ($status)
        {
            Dispatcher::NOT_FOUND => $response->setStatusCode(404)
                ->setContent('404 Not Found'),

            Dispatcher::METHOD_NOT_ALLOWED => $response->setStatusCode(405)
                ->setContent('405 Method Not Allowed'),

            Dispatcher::FOUND => (function () use ($routeInfo, $response)
            {
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];
                $fqcn = "NativePlatform\\Routes\\Controllers\\$class";

                if (!class_exists($fqcn))
                {
                    $response->setStatusCode(500)
                        ->setContent("Controller {$fqcn} not found");
                    return;
                }

                $controller = new $fqcn($this->bridge->container);
                $controller->setRouteParams($vars);
                call_user_func_array([$controller, $method], $vars);
            })(),

            default => $response->setStatusCode(500)
                ->setContent("Unexpected router status code: {$status}"),
        };

        $response->send();

        $this->bridge->container->lazy('app:request');
        $this->bridge->container->lazy('app:response');
    }
}
