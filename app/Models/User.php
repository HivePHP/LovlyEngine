<?php
declare(strict_types=1);

namespace App\Models;

use HivePHP\Model;

class User extends Model
{
    public function getUser(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, name, surname, sex, day, month, year, country, city, email, status, user_real, created_at, last_login FROM users  WHERE id = :id  LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function emailExists(string $email): bool
    {
        return (bool)$this->db->fetch(
            "SELECT id FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );
    }

    public function createUser(array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("createUser: empty data array");
        }

        $columns = array_keys($data);
        $fields  = array_map(fn($c) => ':' . $c, $columns);

        $sql = "INSERT INTO users (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $fields) . ")";

        return $this->db->insert($sql, $data);
    }

    public function countUsers(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM users");
        return (int)$row['total'];
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET last_login = :ts WHERE id = :id",
            ['ts' => date('Y-m-d H:i:s'), 'id' => $id]
        );
    }

    public function statusGet(int $id): ?string
    {
        $row = $this->db->fetch(
            "SELECT status FROM users WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        return $row['status'] ?? null;
    }

    public function statusSet(int $id, string $status): int
    {
        return $this->db->execute(
            "UPDATE users SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $id]
        );
    }

    public function statusDelete(int $id): int
    {
        return $this->db->execute(
            "UPDATE users SET status = NULL WHERE id = :id",
            ['id' => $id]
        );
    }
}
