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

final class PhotoRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(int $albumId, int $userId, string $path, string $thumb, ?string $avatarUrl = null): int
    {
        $sort = $this->maxSortOrder($albumId) + 1;

        $this->db->execute(
            "INSERT INTO photos (album_id, user_id, path, thumb, sort_order, avatar_url)
             VALUES (:album_id, :user_id, :path, :thumb, :sort_order, :avatar_url)",
            [
                'album_id'   => $albumId,
                'user_id'    => $userId,
                'path'       => $path,
                'thumb'      => $thumb,
                'sort_order' => $sort,
                'avatar_url' => $avatarUrl,
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * All avatar-linked photos of an album in upload order, plus an ordinal
     * position. Used to restore the previous avatar when the current one is
     * removed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function avatarPhotos(int $albumId): array
    {
        return $this->db->fetchAll(
            "SELECT id, album_id, path, avatar_url, sort_order
               FROM photos
              WHERE album_id = :album_id AND avatar_url IS NOT NULL AND avatar_url <> ''
              ORDER BY sort_order ASC, id ASC",
            ['album_id' => $albumId]
        );
    }

    public function maxSortOrder(int $albumId): int
    {
        $row = $this->db->fetch(
            "SELECT COALESCE(MAX(sort_order), -1) AS max_sort
               FROM photos
              WHERE album_id = :album_id",
            ['album_id' => $albumId]
        );

        return (int)($row['max_sort'] ?? -1);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForAlbum(int $albumId): array
    {
        return $this->db->fetchAll(
            "SELECT id, album_id, user_id, path, thumb, sort_order, avatar_url, created_at
               FROM photos
              WHERE album_id = :album_id
              ORDER BY sort_order ASC, id ASC",
            ['album_id' => $albumId]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, album_id, user_id, path, thumb, sort_order, avatar_url, created_at
               FROM photos
              WHERE id = :id
              LIMIT 1",
            ['id' => $id]
        ) ?: null;
    }

    public function delete(int $id): void
    {
        $this->db->execute("DELETE FROM photos WHERE id = :id", ['id' => $id]);
    }

    /**
     * The most recently added photos of a user across all their albums
     * (including the protected page album), newest first. Used for the
     * "last photos" block on the profile page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recentForUser(int $userId, int $limit): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.path, p.thumb, p.created_at,
                    a.id AS album_id, a.title AS album_title
               FROM photos p
               JOIN albums a ON a.id = p.album_id
              WHERE a.user_id = :user_id
              ORDER BY p.created_at DESC, p.id DESC
              LIMIT " . max(1, (int)$limit),
            ['user_id' => $userId]
        );
    }

    /**
     * Total number of photos the user has across all their albums.
     */
    public function countForUser(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(p.id) AS cnt
               FROM photos p
               JOIN albums a ON a.id = p.album_id
              WHERE a.user_id = :user_id",
            ['user_id' => $userId]
        );

        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Repoint an album photo to a new avatar crop url (used when re-cropping
     * the avatar without creating a duplicate original).
     */
    public function updateAvatarUrl(int $id, string $avatarUrl): void
    {
        $this->db->execute(
            "UPDATE photos SET avatar_url = :avatar_url WHERE id = :id",
            ['id' => $id, 'avatar_url' => $avatarUrl]
        );
    }

    /**
     * The album photo (with its stored original `path`) that produced the given
     * avatar crop for a user. Used to re-crop the avatar from its original.
     *
     * @return array<string, mixed>|null
     */
    public function findOriginalByAvatar(int $userId, string $avatarUrl): ?array
    {
        if ($avatarUrl === '') {
            return null;
        }

        return $this->db->fetch(
            "SELECT p.id, p.path, p.album_id
               FROM photos p
               JOIN albums a ON a.id = p.album_id
              WHERE a.user_id = :user_id
                AND p.avatar_url = :avatar_url
              ORDER BY p.id ASC
              LIMIT 1",
            ['user_id' => $userId, 'avatar_url' => $avatarUrl]
        ) ?: null;
    }

    /**
     * Reorder an ordered list of photo ids within one album.
     *
     * @param int[] $orderedIds
     */
    public function reorder(int $albumId, array $orderedIds): void
    {
        if (!$orderedIds) {
            return;
        }

        $this->db->execute('START TRANSACTION');

        try {
            foreach (array_values($orderedIds) as $i => $id) {
                $this->db->execute(
                    "UPDATE photos
                        SET sort_order = :sort_order
                      WHERE id = :id AND album_id = :album_id",
                    ['sort_order' => $i, 'id' => (int)$id, 'album_id' => $albumId]
                );
            }
            $this->db->execute('COMMIT');
        } catch (\Throwable $e) {
            $this->db->execute('ROLLBACK');
            throw $e;
        }
    }
}
