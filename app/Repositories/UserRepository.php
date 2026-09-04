<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Repositories;

use HivePHP\Database\Database;

final class UserRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO users
             (name, surname, sex, email, password_hash, city, country, day, month, year)
             VALUES
             (:name, :surname, :sex, :email, :password_hash, :city, :country, :day, :month, :year)",
            $data
        );

        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT id, name, surname, email, password_hash FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        ) ?: null;
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, name, surname, email, avatar FROM users WHERE id = :id LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function findProfileById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, name, surname, avatar, status, sex, city, country, day, month, year, about, interests, favorite_films
             FROM users
             WHERE id = :id
             LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function updateAvatar(int $id, ?string $avatar): void
    {
        $this->db->execute(
            "UPDATE users SET avatar = :avatar WHERE id = :id",
            ['id' => $id, 'avatar' => $avatar]
        );
    }

    public function updateProfile(int $id, string $about, string $interests, string $favoriteFilms): void
    {
        $this->db->execute(
            "UPDATE users SET about = :about, interests = :interests, favorite_films = :favorite_films WHERE id = :id",
            [
                'id'             => $id,
                'about'          => $about,
                'interests'      => $interests,
                'favorite_films' => $favoriteFilms,
            ]
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->execute(
            "UPDATE users SET status = :status WHERE id = :id",
            ['id' => $id, 'status' => $status]
        );
    }

    public function count(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) as cnt FROM users");
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Most recently registered users, excluding one id.
     * Shown in the profile right sidebar as a "Users" block.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recentUsers(int $excludeId, int $limit): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, surname, avatar, city, country
               FROM users
              WHERE id <> :exclude_id
              ORDER BY created_at DESC, id DESC
              LIMIT " . max(1, (int)$limit),
            ['exclude_id' => $excludeId]
        );
    }
}
