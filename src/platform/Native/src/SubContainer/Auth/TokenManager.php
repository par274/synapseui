<?php

namespace NativePlatform\SubContainer\Auth;

use NativePlatform\SubContainer\SecurityConfig;

use Symfony\Component\HttpFoundation\Session\Session;

class TokenManager
{
    protected const TOKEN_LIFETIME = 1800;

    public static function generate(int $userId, Session $session): string
    {
        $raw = bin2hex(random_bytes(32));
        $hash = hash_hmac('sha256', $raw, SecurityConfig::getSecret());
        $token = base64_encode($userId . ':' . $hash);
        $expiresAt = time() + self::TOKEN_LIFETIME;

        $tokens = $session->get(SecurityConfig::RESET_TOKEN_KEY, []);
        $tokens[$token] = [
            'user_id' => $userId,
            'expires' => $expiresAt
        ];
        $session->set(SecurityConfig::RESET_TOKEN_KEY, $tokens);

        return $token;
    }

    public static function validate(string $token, Session $session): ?int
    {
        $tokens = $session->get(SecurityConfig::RESET_TOKEN_KEY, []);

        if (!isset($tokens[$token]))
        {
            return null;
        }

        $data = $tokens[$token];

        if ($data['expires'] < time())
        {
            unset($tokens[$token]);
            $session->set(SecurityConfig::RESET_TOKEN_KEY, $tokens);
            return null;
        }

        unset($tokens[$token]);
        $session->set(SecurityConfig::RESET_TOKEN_KEY, $tokens);
        return $data['user_id'];
    }
}
