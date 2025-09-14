<?php

namespace NativePlatform\SubContainer;

use PlatformBridge\BridgeConfig;

class SecurityConfig
{
    public const CSRF_SESSION_KEY = '_csrf_token';
    public const REMEMBER_COOKIE_NAME = 'remember_token';
    public const REMEMBER_SESSION_KEY = 'remember_tokens';
    public const RESET_TOKEN_KEY = '_reset_tokens';

    protected static ?BridgeConfig $config = null;

    public static function setBridgeConfig(BridgeConfig $config): void
    {
        self::$config = $config;
    }

    public static function getSecret(): string
    {
        return self::$config?->getSecret() ?? 'fallback-secret';
    }

    public static function getEnv(): string
    {
        return self::$config?->getEnv() ?? 'prod';
    }

    public static function getAppUrl(): string
    {
        return self::$config?->getAppUrl() ?? 'http://localhost';
    }
}
