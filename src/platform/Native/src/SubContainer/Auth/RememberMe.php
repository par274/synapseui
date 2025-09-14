<?php

namespace NativePlatform\SubContainer\Auth;

use NativePlatform\SubContainer\SecurityConfig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

class RememberMe
{
    protected const COOKIE_LIFETIME = 60 * 60 * 24 * 30;

    public static function generate(int $userId, Session $session): string
    {
        $raw = bin2hex(random_bytes(32));
        $hash = hash_hmac('sha256', $raw, SecurityConfig::getSecret());
        $token = base64_encode($userId . ':' . $hash);

        $tokens = $session->get(SecurityConfig::REMEMBER_SESSION_KEY, []);
        $tokens[$userId] = $token;
        $session->set(SecurityConfig::REMEMBER_SESSION_KEY, $tokens);

        return $token;
    }

    public static function createCookie(string $token): Cookie
    {
        return new Cookie(
            SecurityConfig::REMEMBER_COOKIE_NAME,
            $token,
            time() + self::COOKIE_LIFETIME,
            '/',
            null,
            true,  // Secure
            true,  // HttpOnly
            false,
            Cookie::SAMESITE_STRICT
        );
    }

    public static function validateFromCookie(Request $request, Session $session): bool
    {
        $cookie = $request->cookies->get(SecurityConfig::REMEMBER_COOKIE_NAME);
        if (!$cookie)
        {
            return false;
        }

        $decoded = base64_decode($cookie);
        if (!$decoded || !str_contains($decoded, ':'))
        {
            return false;
        }

        [$userId, $hash] = explode(':', $decoded, 2);
        $tokens = $session->get(SecurityConfig::REMEMBER_SESSION_KEY, []);
        $expected = $tokens[$userId] ?? null;

        if ($expected !== $cookie)
        {
            return false;
        }

        $session->set('user_id', (int)$userId);
        return true;
    }
}
