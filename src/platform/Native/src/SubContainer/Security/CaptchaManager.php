<?php

namespace NativePlatform\SubContainer\Security;

use PlatformBridge\BridgeConfig;
use NativePlatform\SubContainer\Security\GoogleRecaptchaValidator;
use NativePlatform\SubContainer\Security\CloudflareTurnstileValidator;

use Symfony\Component\HttpFoundation\Request;

class CaptchaManager
{
    protected string $provider;
    protected BridgeConfig $config;
    protected GoogleRecaptchaValidator $google;
    protected CloudflareTurnstileValidator $cloudflare;

    public function __construct(
        BridgeConfig $config,
        GoogleRecaptchaValidator $google,
        CloudflareTurnstileValidator $cloudflare
    )
    {
        $this->config = $config;
        $this->provider = $config->get('APP_CAPTCHA_PROVIDER', 'none');
        $this->google = $google;
        $this->cloudflare = $cloudflare;
    }

    public function validate(Request $request): bool
    {
        return match ($this->provider)
        {
            'google' => $this->google->validate($request),
            'cloudflare' => $this->cloudflare->validate($request),
            default => true,
        };
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getSiteKey(): ?string
    {
        return match ($this->provider)
        {
            'google' => $this->config->get('APP_CAPTCHA_SITE_KEY'),
            'cloudflare' => $this->config->get('APP_CAPTCHA_SITE_KEY'),
            default => null,
        };
    }

    public function getRenderScriptUrl(): ?string
    {
        return match ($this->provider)
        {
            'google'     => 'https://www.google.com/recaptcha/api.js',
            'cloudflare' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            default      => null,
        };
    }
}
