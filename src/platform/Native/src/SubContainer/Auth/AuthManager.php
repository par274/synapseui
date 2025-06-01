<?php

namespace NativePlatform\SubContainer\Auth;

use NativePlatform\Db\Entities\User;
use NativePlatform\SubContainer\Auth\TokenManager;
use NativePlatform\SubContainer\Auth\RememberMe;
use NativePlatform\SubContainer\SecurityConfig;
use NativePlatform\SubContainer\Security\CaptchaManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthManager
{
    protected Session $session;
    protected Request $request;
    protected Response $response;
    protected User $userEntity;
    protected CaptchaManager $captcha;

    public function __construct(Session $session, Request $request, Response $response, User $userEntity, CaptchaManager $captcha)
    {
        $this->session = $session;
        $this->request = $request;
        $this->response = $response;
        $this->userEntity = $userEntity;
        $this->captcha = $captcha;
        $this->session->start();
    }

    public function login(string $username, string $password, bool $remember = false): bool
    {
        $user = $this->userEntity->findByUsername($username);
        if (!$user || !password_verify($password, $user['password']))
        {
            return false;
        }

        $this->session->set('user_id', $user['id']);

        if ($remember)
        {
            $token = RememberMe::generate($user['id'], $this->session);
            $cookie = RememberMe::createCookie($token);
            $this->response->headers->setCookie($cookie);
        }

        return true;
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->response->headers->clearCookie(SecurityConfig::REMEMBER_COOKIE_NAME);
    }

    public function check(): bool
    {
        return $this->session->has('user_id') || RememberMe::validateFromCookie($this->request, $this->session);
    }

    public function user(): ?array
    {
        $id = $this->session->get('user_id');
        return $id ? $this->userEntity->findById($id) : null;
    }

    public function forgotPassword(string $email): ?string
    {
        $user = $this->userEntity->findByEmail($email);
        if (!$user) return null;

        return TokenManager::generate($user['id'], $this->session);
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $userId = TokenManager::validate($token, $this->session);
        if (!$userId) return false;

        return $this->userEntity->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    public function register(string $username, string $email, string $password): bool
    {
        if ($this->userEntity->findByUsername($username) || $this->userEntity->findByEmail($email))
        {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $this->userEntity->create([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getCaptcha(): CaptchaManager
    {
        return $this->captcha;
    }
}
