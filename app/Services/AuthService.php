<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRememberRepository;
use App\Repositories\UserRepository;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Security\CsrfToken;

final class AuthService
{
    private const COOKIE_NAME = 'remember_token';
    private const TTL = 2592000; // 30 days

    private bool $resolved = false;
    private ?array $user = null;

    public function __construct(
        private readonly Cookie $cookies,
        private readonly Request $request,
        private readonly UserRepository $users,
        private readonly UserRememberRepository $tokens,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?array
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        if (!empty($_SESSION['uid'])) {
            return $this->user = $this->users->findById((int)$_SESSION['uid']);
        }

        if ($this->cookies->has(self::COOKIE_NAME)) {
            return $this->user = $this->loginByRememberToken(
                $this->cookies->get(self::COOKIE_NAME)
            );
        }

        return null;
    }

    public function login(int $userId, bool $remember): void
    {
        session_regenerate_id(true);
        $_SESSION['uid'] = $userId;
        CsrfToken::refresh();

        if ($remember) {
            $this->rotateRememberToken($userId);
        }
    }

    public function logout(): void
    {
        if ($this->cookies->has(self::COOKIE_NAME)) {
            $this->deleteCurrentRememberToken(
                $this->cookies->get(self::COOKIE_NAME)
            );
            $this->cookies->delete(self::COOKIE_NAME);
        }

        unset($_SESSION['uid']);
        CsrfToken::refresh();
        session_regenerate_id(true);
    }

    private function rotateRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);

        $this->tokens->deleteAllByUserId($userId);

        $this->tokens->create(
            $userId,
            $hash,
            $this->request->userAgent(),
            $this->request->ip(),
            date('Y-m-d H:i:s', time() + self::TTL)
        );

        $this->cookies->set(self::COOKIE_NAME, $token, self::TTL);
    }

    private function loginByRememberToken(string $token): ?array
    {
        $hash = hash('sha256', $token);

        $row = $this->tokens->findValidByTokenHash($hash, $this->request->userAgent());

        if (!$row) {
            return null;
        }

        $user = $this->users->findById((int)$row['user_id']);
        if (!$user) {
            return null;
        }

        $this->login($user['id'], true);
        return $user;
    }

    private function deleteCurrentRememberToken(string $token): void
    {
        $this->tokens->deleteByTokenHash(hash('sha256', $token));
    }
}
