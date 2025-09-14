<?php

namespace PlatformBridge;

use PlatformBridge\RouteCollection;

use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

use FastRoute\{
    RouteCollector as FastRouteCollector,
    Dispatcher,
    function simpleDispatcher
};

class BridgeFoundation
{
    protected Bridge $bridge;

    public function __construct()
    {
        $this->bridge = new Bridge();
    }

    /**
     * Runs the application by processing the HTTP request and dispatching to the appropriate route.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var Request $request */
        $request = $this->bridge->container->get('app:request');

        /** @var Response $response */
        $response = $this->bridge->container->get('app:response');

        $httpMethod = $request->getMethod();
        $requestUri = $request->getPathInfo();

        RouteLoader::load(NATIVE_PLATFORM_DIR . '/src/Routes/Collections/');

        $dispatcher = simpleDispatcher(function (FastRouteCollector $r)
        {
            RouteCollection::loadInto($r);
        });

        $routeInfo = $dispatcher->dispatch($httpMethod, $requestUri);

        match ($routeInfo[0])
        {
            Dispatcher::NOT_FOUND => $response
                ->setStatusCode(404)
                ->setContent('404 Not Found'),

            Dispatcher::METHOD_NOT_ALLOWED => $response
                ->setStatusCode(405)
                ->setContent('405 Method Not Allowed'),

            Dispatcher::FOUND => (function () use ($routeInfo, $response)
            {
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                if ($handler instanceof \Closure)
                {
                    return $handler($vars);
                }

                [$class, $method] = $handler;

                if (!class_exists($class))
                {
                    $response->setStatusCode(500)
                        ->setContent("Controller {$class} not found");
                    return;
                }

                $controller = new $class($this->bridge->container);
                $controller->setRouteParams($vars);

                return $controller->{$method}($vars);
            })(),

            default => $response
                ->setStatusCode(500)
                ->setContent("Unexpected router status code: {$routeInfo[0]}"),
        };

        $response->send();

        $this->bridge->container->lazy('app:request');
        $this->bridge->container->lazy('app:response');
    }
}
