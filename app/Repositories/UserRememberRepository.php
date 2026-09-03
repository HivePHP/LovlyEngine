<?php

declare(strict_types=1);

namespace App\Repositories;

use HivePHP\Database\Database;

final class UserRememberRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(int $userId, string $tokenHash, string $userAgent, string $ip, string $expiresAt): void
    {
        $this->db->execute(
            "INSERT INTO user_remember_tokens (user_id, token_hash, user_agent, ip, expires_at)
             VALUES (:user_id, :token_hash, :user_agent, :ip, :expires_at)",
            [
                'user_id'     => $userId,
                'token_hash'  => $tokenHash,
                'user_agent'  => $userAgent,
                'ip'          => $ip,
                'expires_at'  => $expiresAt,
            ]
        );
    }

    public function deleteByTokenHash(string $tokenHash): void
    {
        $this->db->execute(
            "DELETE FROM user_remember_tokens WHERE token_hash = :token_hash",
            ['token_hash' => $tokenHash]
        );
    }

    public function deleteAllByUserId(int $userId): void
    {
        $this->db->execute(
            "DELETE FROM user_remember_tokens WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
    }

    public function findValidByTokenHash(string $tokenHash, string $userAgent): ?array
    {
        return $this->db->fetch(
            "SELECT id, user_id, token_hash, user_agent, ip, expires_at
             FROM user_remember_tokens
             WHERE token_hash = :token_hash
               AND user_agent = :user_agent
               AND expires_at > NOW()
             LIMIT 1",
            [
                'token_hash' => $tokenHash,
                'user_agent' => $userAgent,
            ]
        ) ?: null;
    }

    public function deleteExpired(): void
    {
        $this->db->execute(
            "DELETE FROM user_remember_tokens WHERE expires_at <= NOW()"
        );
    }
}
