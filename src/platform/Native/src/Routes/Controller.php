<?php

namespace NativePlatform\Routes;

use NativePlatform\SubContainer\ServiceContainer;

use Symfony\Component\Translation\Translator;

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

    protected function getJsTranslationList(Translator $translator): array
    {
        $jsTranslations = [];
        foreach ($translator->getCatalogue('en')->all('_meta') as $key => $value)
        {
            if (str_starts_with($key, 'js_allowed.') && $value === true)
            {
                $parts = ltrim($key, 'js_allowed.');
                $parts = explode('.', $parts, 2);
                $domain = $parts[0];
                $realKey = $parts[1] ?? '';

                $jsTranslations["{$domain}.{$realKey}"] = $translator->trans($realKey, [], $domain);
            }
        }

        return $jsTranslations;
    }
}
