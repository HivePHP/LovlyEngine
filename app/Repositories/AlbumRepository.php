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

final class AlbumRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(int $userId, string $title, string $description, int $sortOrder, bool $isProtected = false): int
    {
        $this->db->execute(
            "INSERT INTO albums (user_id, title, description, sort_order, is_protected)
             VALUES (:user_id, :title, :description, :sort_order, :is_protected)",
            [
                'user_id'     => $userId,
                'title'       => $title,
                'description' => $description,
                'sort_order'  => $sortOrder,
                'is_protected'=> $isProtected ? 1 : 0,
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT a.*, u.name AS owner_name, u.surname AS owner_surname
             FROM albums a
             JOIN users u ON u.id = a.user_id
             WHERE a.id = :id
             LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    /**
     * The user's protected album that stores original avatars, if it exists.
     */
    public function findProtectedByUserId(int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM albums
              WHERE user_id = :user_id AND is_protected = 1
              ORDER BY id ASC
              LIMIT 1",
            ['user_id' => $userId]
        ) ?: null;
    }

    /**
     * All albums of a user, each with `photo_count` and `cover` (full url of the
     * first photo), ordered by sort_order then id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allForUser(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT a.id, a.title, a.description, a.sort_order, a.is_protected,
                    COUNT(p.id) AS photo_count,
                    (SELECT p2.path
                       FROM photos p2
                      WHERE p2.album_id = a.id
                      ORDER BY p2.sort_order ASC, p2.id ASC
                      LIMIT 1) AS cover
               FROM albums a
               LEFT JOIN photos p ON p.album_id = a.id
              WHERE a.user_id = :user_id
              GROUP BY a.id, a.title, a.description, a.sort_order, a.is_protected
              ORDER BY a.sort_order ASC, a.id ASC",
            ['user_id' => $userId]
        );

        foreach ($rows as &$row) {
            $row['photo_count'] = (int)$row['photo_count'];
            $row['sort_order']  = (int)$row['sort_order'];
            $row['is_protected'] = (int)$row['is_protected'] === 1;
        }

        return $rows;
    }

    public function maxSortOrder(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COALESCE(MAX(sort_order), -1) AS max_sort
               FROM albums
              WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        return (int)($row['max_sort'] ?? -1);
    }

    /**
     * Delete a single album row (photo rows are removed by FK CASCADE).
     * Protected (system) albums can never be deleted.
     */
    public function delete(int $id): void
    {
        $this->db->execute(
            "DELETE FROM albums WHERE id = :id AND is_protected = 0",
            ['id' => $id]
        );
    }

    /**
     * Reorder an ordered list of album ids belonging to one user.
     *
     * @param int[] $orderedIds
     */
    public function reorder(int $userId, array $orderedIds): void
    {
        if (!$orderedIds) {
            return;
        }

        $this->db->execute('START TRANSACTION');

        try {
            foreach (array_values($orderedIds) as $i => $id) {
                $this->db->execute(
                    "UPDATE albums
                        SET sort_order = :sort_order
                      WHERE id = :id AND user_id = :user_id",
                    ['sort_order' => $i, 'id' => (int)$id, 'user_id' => $userId]
                );
            }
            $this->db->execute('COMMIT');
        } catch (\Throwable $e) {
            $this->db->execute('ROLLBACK');
            throw $e;
        }
    }
}
