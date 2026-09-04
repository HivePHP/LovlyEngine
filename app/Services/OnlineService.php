<?php
declare(strict_types=1);

namespace App\Services;

use HivePHP\Database\Database;

final class OnlineService
{
    public function __construct(
        private readonly Database $db
    ) {}

    /**
     * Update user's last_seen_at to current timestamp.
     * Called on each heartbeat.
     */
    public function heartbeat(int $userId): void
    {
        $this->db->execute(
            'UPDATE users SET last_seen_at = NOW() WHERE id = :id',
            ['id' => $userId]
        );
    }

    /**
     * Mark user as offline immediately (set last_seen_at to now minus 10 minutes).
     * Called on browser close via sendBeacon.
     */
    public function markOffline(int $userId): void
    {
        $this->db->execute(
            'UPDATE users SET last_seen_at = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE id = :id',
            ['id' => $userId]
        );
    }

    /**
     * Check if a user is considered online (active within the last 5 minutes).
     */
    public function isOnline(int $userId): bool
    {
        $row = $this->db->fetch(
            'SELECT 1 FROM users WHERE id = :id AND last_seen_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)',
            ['id' => $userId]
        );
        return $row !== null;
    }

    /**
     * Get online status for multiple users at once.
     * Returns array of user_id => bool.
     */
    public function getOnlineStatuses(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT id FROM users
                WHERE id IN ($placeholders)
                  AND last_seen_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)";

        $rows = $this->db->fetchAll($sql, $userIds);
        $online = [];
        foreach ($rows as $row) {
            $online[(int)$row['id']] = true;
        }

        $result = [];
        foreach ($userIds as $uid) {
            $result[$uid] = isset($online[$uid]);
        }
        return $result;
    }

    /**
     * Format last_seen_at into a human-readable Russian string.
     * Returns null if user is currently online.
     */
    public function formatLastSeen(?string $lastSeenAt): ?string
    {
        if ($lastSeenAt === null) {
            return null;
        }

        $now = time();
        $then = strtotime($lastSeenAt);
        $diff = $now - $then;

        if ($diff < 0) {
            return null;
        }

        // Online (less than 2 minutes)
        if ($diff < 120) {
            return null; // Caller should show "online" instead
        }

        // Minutes
        if ($diff < 3600) {
            $mins = (int)($diff / 60);
            return $this->pluralize($mins, 'минуту', 'минуты', 'минут') . ' назад';
        }

        // Hours
        if ($diff < 86400) {
            $hours = (int)($diff / 3600);
            return $this->pluralize($hours, 'час', 'часа', 'часов') . ' назад';
        }

        // Days
        $days = (int)($diff / 86400);
        if ($days < 7) {
            return $this->pluralize($days, 'день', 'дня', 'дней') . ' назад';
        }

        // Weeks
        if ($days < 30) {
            $weeks = (int)($days / 7);
            return $this->pluralize($weeks, 'неделю', 'недели', 'недель') . ' назад';
        }

        // Months
        $months = (int)($days / 30);
        if ($months < 12) {
            return $this->pluralize($months, 'месяц', 'месяца', 'месяцев') . ' назад';
        }

        // Years
        $years = (int)($days / 365);
        return $this->pluralize($years, 'год', 'года', 'лет') . ' назад';
    }

    private function pluralize(int $n, string $one, string $few, string $many): string
    {
        $mod10 = $n % 10;
        $mod100 = $n % 100;

        if ($mod100 >= 11 && $mod100 <= 19) {
            return $n . ' ' . $many;
        }
        if ($mod10 === 1) {
            return $n . ' ' . $one;
        }
        if ($mod10 >= 2 && $mod10 <= 4) {
            return $n . ' ' . $few;
        }
        return $n . ' ' . $many;
    }
}
