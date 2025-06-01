<?php

namespace NativePlatform\Routes;

use NativePlatform\SubContainer\ServiceContainer;

abstract class Controller
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function setRouteParams(array $params)
    {
        $this->container->set('routing:params', fn() => $params);
    }
}
