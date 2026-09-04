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

final class NotificationRepository
{
    public function __construct(
        private readonly Database $db
    ) {}

    /**
     * Persist a notification for a recipient.
     */
    public function create(
        int $userId,
        string $type,
        string $section,
        ?int $actorId,
        array $data = [],
        ?string $link = null
    ): int {
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, section, actor_id, data, link)
             VALUES (:user_id, :type, :section, :actor_id, :data, :link)",
            [
                'user_id'  => $userId,
                'type'     => $type,
                'section'  => $section,
                'actor_id' => $actorId,
                'data'     => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
                'link'     => $link,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Recent notifications for a user (newest first), pre-joined with the actor.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $userId, int $limit = 30): array
    {
        $limit = max(1, min(50, $limit));

        $rows = $this->db->fetchAll(
            "SELECT n.id, n.type, n.section, n.actor_id, n.data, n.link, n.is_read,
                    n.created_at,
                    a.name AS actor_name, a.surname AS actor_surname, a.avatar AS actor_avatar
               FROM notifications n
               LEFT JOIN users a ON a.id = n.actor_id
              WHERE n.user_id = :user_id
              ORDER BY n.id DESC
              LIMIT {$limit}",
            ['user_id' => $userId]
        );

        foreach ($rows as &$row) {
            $row['data'] = $row['data'] ? json_decode($row['data'], true) : [];
            if (!is_array($row['data'])) {
                $row['data'] = [];
            }
            $row['is_read'] = (bool) $row['is_read'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Total number of unread notifications for a user.
     */
    public function countUnread(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c
               FROM notifications
              WHERE user_id = :user_id AND is_read = 0",
            ['user_id' => $userId]
        );

        return (int) ($row['c'] ?? 0);
    }

    /**
     * Unread counts grouped by section, for sidebar badges.
     *
     * @return array<string, int>
     */
    public function countUnreadBySection(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT section, COUNT(*) AS c
               FROM notifications
              WHERE user_id = :user_id AND is_read = 0
              GROUP BY section",
            ['user_id' => $userId]
        );

        $map = [];
        foreach ($rows as $row) {
            $map[$row['section']] = (int) $row['c'];
        }

        return $map;
    }

    /**
     * Mark one notification as read (must belong to the user).
     */
    public function markRead(int $userId, int $notificationId): bool
    {
        $this->db->execute(
            "UPDATE notifications
                SET is_read = 1
              WHERE id = :id AND user_id = :user_id",
            ['id' => $notificationId, 'user_id' => $userId]
        );

        return true;
    }

    /**
     * Mark the "incoming friend request" notification from $otherId to $userId
     * as read — used when the user accepts/declines the request, so the badge
     * no longer counts it as actionable.
     */
    public function markIncomingResolved(int $userId, int $otherId): void
    {
        $this->markFrom($userId, $otherId, 'friend.request');
    }

    /**
     * Mark as read all unread notifications of a given $type caused by $otherId
     * and addressed to $userId.
     */
    public function markFrom(int $userId, int $otherId, string $type): void
    {
        $this->db->execute(
            "UPDATE notifications
                SET is_read = 1
              WHERE user_id = :uid AND actor_id = :aid
                AND type = :type AND is_read = 0",
            ['uid' => $userId, 'aid' => $otherId, 'type' => $type]
        );
    }

    /**
     * Mark a whole section as read for a user, returning the number affected.
     */
    public function markSectionRead(int $userId, string $section): int
    {
        $this->db->execute(
            "UPDATE notifications
                SET is_read = 1
              WHERE user_id = :user_id AND section = :section AND is_read = 0",
            ['user_id' => $userId, 'section' => $section]
        );

        return 0; // info only; see countUnreadBySection for authoritative badges
    }

    /**
     * Mark every notification read for a user.
     */
    public function markAllRead(int $userId): void
    {
        $this->db->execute(
            "UPDATE notifications
                SET is_read = 1
              WHERE user_id = :user_id AND is_read = 0",
            ['user_id' => $userId]
        );
    }

    /**
     * Remove notifications older than $ttlDays days (call from a scheduled job
     * or the 'guest' pipeline occasionally).
     */
    public function pruneOlderThan(int $ttlDays): int
    {
        if ($ttlDays <= 0) {
            return 0;
        }

        $this->db->execute(
            "DELETE FROM notifications
              WHERE created_at < (NOW() - INTERVAL :days DAY)",
            ['days' => $ttlDays]
        );

        return 0;
    }

    /**
     * Delete a single notification (must belong to the user).
     */
    public function delete(int $userId, int $notificationId): bool
    {
        $this->db->execute(
            "DELETE FROM notifications
              WHERE id = :id AND user_id = :user_id",
            ['id' => $notificationId, 'user_id' => $userId]
        );

        return true;
    }

    /**
     * Delete all notifications for a user.
     */
    public function deleteAll(int $userId): bool
    {
        $this->db->execute(
            "DELETE FROM notifications
              WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        return true;
    }
}
