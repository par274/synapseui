<?php

namespace NativePlatform\Adapters;

use GuzzleHttp\Client;

abstract class AdapterClient
{
    protected Client $client;
    protected string $baseUrl;

    public function setConfig(array $config): void
    {
        $this->baseUrl = $config['base_url'];

        $clientConfig = [];
        $clientConfig['base_uri'] = $this->baseUrl;
        if (isset($config['api_key']) && !is_null($config['api_key']))
        {
            $clientConfig['headers'] = [
                'Authorization' => "Bearer {$config['api_key']}"
            ];
        }
        $clientConfig['http_errors'] = false;

        $this->client = new Client($clientConfig);
    }
}
