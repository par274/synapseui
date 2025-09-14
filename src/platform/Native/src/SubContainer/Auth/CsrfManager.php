<?php

namespace NativePlatform\SubContainer\Auth;

use NativePlatform\SubContainer\SecurityConfig;

use Symfony\Component\HttpFoundation\Session\Session;

class CsrfManager
{
    public static function generate(Session $session): string
    {
        $token = bin2hex(random_bytes(32));
        $session->set(SecurityConfig::CSRF_SESSION_KEY, $token);
        return $token;
    }

    public static function get(Session $session): string
    {
        if (!$session->has(SecurityConfig::CSRF_SESSION_KEY))
        {
            return self::generate($session);
        }

        return $session->get(SecurityConfig::CSRF_SESSION_KEY);
    }

    public static function validate(Session $session, string $inputToken): bool
    {
        $storedToken = $session->get(SecurityConfig::CSRF_SESSION_KEY);

        return $storedToken !== null && hash_equals($storedToken, $inputToken);
    }
}
