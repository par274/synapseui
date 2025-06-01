<?php

namespace NativePlatform\SubContainer\Security;

use PlatformBridge\BridgeConfig;

use Symfony\Component\HttpFoundation\Request;

class CloudflareTurnstileValidator
{
    protected string $secret;

    public function __construct(BridgeConfig $config)
    {
        $this->secret = $config->get('APP_CAPTCHA_SECRET_KEY');
    }

    public function validate(Request $request): bool
    {
        $token = $request->request->get('cf-turnstile-response');

        if (!$token)
        {
            return false;
        }

        $postdata = http_build_query([
            'secret' => $this->secret,
            'response' => $token,
            'remoteip' => $request->getClientIp(),
        ]);

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postdata,
            ]
        ];

        $context = stream_context_create($opts);
        $result = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
        $json = json_decode($result, true);

        return $json['success'] ?? false;
    }
}
