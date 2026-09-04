<?php
declare(strict_types=1);

namespace App\Repositories;

use HivePHP\Database\Database;

final class DocumentRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(int $userId, string $name, string $type, int $size, string $path): int
    {
        $this->db->execute(
            "INSERT INTO documents (user_id, name, type, size, path)
             VALUES (:user_id, :name, :type, :size, :path)",
            [
                'user_id' => $userId,
                'name'    => $name,
                'type'    => $type,
                'size'    => $size,
                'path'    => $path,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function allForUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, type, size, path, created_at
               FROM documents
              WHERE user_id = :user_id
              ORDER BY created_at DESC, id DESC",
            ['user_id' => $userId]
        );
    }

    public function recentForUser(int $userId, int $limit): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, type, size, path, created_at
               FROM documents
              WHERE user_id = :user_id
              ORDER BY created_at DESC, id DESC
              LIMIT " . max(1, (int)$limit),
            ['user_id' => $userId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, user_id, name, type, size, path, created_at
               FROM documents
              WHERE id = :id
              LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function delete(int $userId, int $id): bool
    {
        $this->db->execute(
            "DELETE FROM documents WHERE id = :id AND user_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );

        return true;
    }

    public function countForUser(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM documents WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        return (int) ($row['c'] ?? 0);
    }

    public function totalSizeForUser(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(size), 0) AS total FROM documents WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        return (int) ($row['total'] ?? 0);
    }
}
