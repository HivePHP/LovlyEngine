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
            "SELECT id, name, surname, email FROM users WHERE id = :id LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function findProfileById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, name, surname, sex, city, country, day, month, year, about, interests, favorite_films
             FROM users
             WHERE id = :id
             LIMIT 1",
            ['id' => $id]
        ) ?: null;
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

    public function count(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) as cnt FROM users");
        return (int)($row['cnt'] ?? 0);
    }
}
