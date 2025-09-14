<?php

namespace NativePlatform\SubContainer\Security;

use PlatformBridge\BridgeConfig;

use Symfony\Component\HttpFoundation\Request;

class GoogleRecaptchaValidator
{
    protected string $secret;

    public function __construct(BridgeConfig $config)
    {
        $this->secret = $config->get('APP_CAPTCHA_SECRET_KEY');
    }

    public function validate(Request $request): bool
    {
        $token = $request->request->get('g-recaptcha-response');
        if (!$token)
        {
            return false;
        }

        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='
            . $this->secret . '&response=' . $token . '&remoteip=' . $request->getClientIp());

        $result = json_decode($response, true);

        return $result['success'] ?? false;
    }
}
