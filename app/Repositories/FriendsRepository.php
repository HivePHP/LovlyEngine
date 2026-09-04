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

final class FriendsRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    /**
     * Relation between two users from $self's point of view.
     *
     * @return string 'none' | 'outgoing' | 'incoming' | 'friends'
     */
    public function relation(int $self, int $other): string
    {
        if ($self === $other) {
            return 'none';
        }

        $row = $this->find($self, $other);

        if (!$row) {
            return 'none';
        }

        if ($row['status'] === 'accepted') {
            return 'friends';
        }

        return (int)$row['user_id'] === $self ? 'outgoing' : 'incoming';
    }

    /**
     * Send a friend request (or accept an inbound one if the other side has
     * already asked us). Returns the resulting relation for $self.
     */
    public function add(int $self, int $other): string
    {
        $current = $this->relation($self, $other);

        if ($current === 'friends') {
            return 'friends';
        }

        if ($current === 'outgoing') {
            return 'outgoing';
        }

        if ($current === 'incoming') {
            // The other side already requested us -> become friends right away.
            $this->db->execute(
                "UPDATE friends
                    SET status = 'accepted'
                  WHERE user_id = :other AND friend_id = :self AND status = 'pending'
                  LIMIT 1",
                ['other' => $other, 'self' => $self]
            );
            return 'friends';
        }

        $this->db->execute(
            "INSERT INTO friends (user_id, friend_id, status)
             VALUES (:self, :other, 'pending')
             ON DUPLICATE KEY UPDATE status = VALUES(status)",
            ['self' => $self, 'other' => $other]
        );

        return 'outgoing';
    }

    /**
     * Accept an inbound pending request from $other.
     */
    public function accept(int $self, int $other): bool
    {
        $this->db->execute(
            "UPDATE friends
                SET status = 'accepted'
              WHERE user_id = :other AND friend_id = :self AND status = 'pending'
              LIMIT 1",
            ['other' => $other, 'self' => $self]
        );

        return $this->relation($self, $other) === 'friends';
    }

    /**
     * Decline an inbound pending request from $other, or cancel our own
     * outgoing one.
     */
    public function decline(int $self, int $other): bool
    {
        $this->db->execute(
            "DELETE FROM friends
              WHERE (user_id = :self1 AND friend_id = :other1)
                 OR (user_id = :other2 AND friend_id = :self2)
              LIMIT 1",
            ['self1' => $self, 'other1' => $other, 'other2' => $other, 'self2' => $self]
        );

        return $this->relation($self, $other) === 'none';
    }

    /**
     * Remove an existing friendship (unfriend). Also clears any pending rows.
     */
    public function remove(int $self, int $other): bool
    {
        $this->db->execute(
            "DELETE FROM friends
              WHERE (user_id = :self1 AND friend_id = :other1)
                 OR (user_id = :other2 AND friend_id = :self2)
              LIMIT 1",
            ['self1' => $self, 'other1' => $other, 'other2' => $other, 'self2' => $self]
        );

        return $this->relation($self, $other) === 'none';
    }

    /**
     * Ids of confirmed friends of a user (both directions).
     *
     * @return array<int, int>
     */
    public function friendIds(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT fid FROM (
                 SELECT friend_id AS fid
                   FROM friends
                  WHERE user_id = :u1 AND status = 'accepted'
                 UNION
                 SELECT user_id AS fid
                   FROM friends
                  WHERE friend_id = :u2 AND status = 'accepted'
             ) AS ids",
            ['u1' => $userId, 'u2' => $userId]
        );

        return array_map(static fn(array $r): int => (int)$r['fid'], $rows);
    }

    /**
     * Confirmed friends with their profile data.
     *
     * @return array<int, array<string, mixed>>
     */
    public function friends(int $userId): array
    {
        $ids = $this->friendIds($userId);
        if (!$ids) {
            return [];
        }

        return $this->usersByIds($ids);
    }

    /**
     * Inbound pending friend requests (people who asked $userId).
     *
     * @return array<int, array<string, mixed>>
     */
    public function incomingRequests(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT f.id, u.id AS friend_user_id, u.name, u.surname, u.avatar, u.city, u.country
               FROM friends f
               JOIN users u ON u.id = f.user_id
              WHERE f.friend_id = :u AND f.status = 'pending'
              ORDER BY f.created_at DESC, f.id DESC",
            ['u' => $userId]
        );

        return $rows;
    }

    /**
     * Outbound pending requests ($userId asked someone, still pending).
     *
     * @return array<int, array<string, mixed>>
     */
    public function outgoingRequests(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT f.id, u.id AS friend_user_id, u.name, u.surname, u.avatar, u.city, u.country
               FROM friends f
               JOIN users u ON u.id = f.friend_id
              WHERE f.user_id = :u AND f.status = 'pending'
              ORDER BY f.created_at DESC, f.id DESC",
            ['u' => $userId]
        );

        return $rows;
    }

    public function countFriends(int $userId): int
    {
        return count($this->friendIds($userId));
    }

    /**
     * Number of confirmed friends shared between two users.
     */
    public function countCommonFriends(int $a, int $b): int
    {
        if ($a === $b) {
            return 0;
        }
        $aIds = $this->friendIds($a);
        if (!$aIds) {
            return 0;
        }
        $bIds = array_flip($this->friendIds($b));
        $count = 0;
        foreach ($aIds as $id) {
            if (isset($bIds[$id])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Profile data of friends shared between two users.
     *
     * @return array<int, array<string, mixed>>
     */
    public function commonFriends(int $a, int $b): array
    {
        if ($a === $b) {
            return [];
        }

        $bIds = array_flip($this->friendIds($b));
        $common = [];
        foreach ($this->friendIds($a) as $id) {
            if (isset($bIds[$id])) {
                $common[] = (int)$id;
            }
        }

        return $this->usersByIds($common);
    }

    private function find(int $a, int $b): ?array
    {
        return $this->db->fetch(
            "SELECT id, user_id, friend_id, status
               FROM friends
              WHERE (user_id = :a1 AND friend_id = :b1)
                 OR (user_id = :b2 AND friend_id = :a2)
              LIMIT 1",
            ['a1' => $a, 'b1' => $b, 'b2' => $b, 'a2' => $a]
        ) ?: null;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, array<string, mixed>>
     */
    private function usersByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT id, name, surname, avatar, city, country
                    FROM users
                   WHERE id IN ({$placeholders})
                   ORDER BY id ASC";

        $params = [];
        foreach ($ids as $id) {
            $params[] = $id;
        }

        $rows = $this->db->fetchAll($query, $params);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row;
        }

        // Preserve requested order.
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $ordered[] = $map[$id];
            }
        }

        return $ordered;
    }
}
