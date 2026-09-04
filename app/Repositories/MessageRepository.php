<?php
declare(strict_types=1);

namespace App\Repositories;

use HivePHP\Database\Database;

final class MessageRepository
{
    private const THREAD_LIMIT = 100;

    public function __construct(
        private readonly Database $db
    ) {}

    public function ensureConversation(int $a, int $b): int
    {
        [$low, $high] = $a <= $b ? [$a, $b] : [$b, $a];

        $row = $this->db->fetch(
            "SELECT id FROM conversations WHERE user_low = :l AND user_high = :h",
            ['l' => $low, 'h' => $high]
        );

        if ($row) {
            return (int)$row['id'];
        }

        $this->db->execute(
            "INSERT INTO conversations (user_low, user_high) VALUES (:l, :h)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)",
            ['l' => $low, 'h' => $high]
        );

        return (int)$this->db->lastInsertId();
    }

    public function send(int $from, int $to, string $body, ?int $forwardedFrom = null, ?int $forwardedBy = null): array
    {
        $conversationId = $this->ensureConversation($from, $to);

        $this->db->execute(
            "INSERT INTO messages (conversation_id, sender_id, recipient_id, body, forwarded_from, forwarded_by)
             VALUES (:conv, :from, :to, :body, :ff, :fb)",
            ['conv' => $conversationId, 'from' => $from, 'to' => $to, 'body' => $body, 'ff' => $forwardedFrom, 'fb' => $forwardedBy]
        );
        $messageId = (int)$this->db->lastInsertId();

        $this->db->execute(
            "UPDATE conversations
                SET last_message_id = :mid, last_message_at = NOW()
              WHERE id = :cid",
            ['mid' => $messageId, 'cid' => $conversationId]
        );

        $row = $this->db->fetch(
            "SELECT id, conversation_id, sender_id, recipient_id, body, is_read, created_at,
                    forwarded_from, forwarded_by
               FROM messages WHERE id = :id",
            ['id' => $messageId]
        );
        if (!$row) {
            return ['id' => $messageId, 'conversation_id' => $conversationId, 'sender_id' => $from, 'recipient_id' => $to, 'body' => $body, 'is_read' => false, 'created_at' => null, 'forwarded_from' => null, 'forwarded_by' => null];
        }
        $row['conversation_id'] = (int)$row['conversation_id'];

        return $row;
    }

    /**
     * Conversation list — excludes conversations hidden by the current user.
     */
    public function conversations(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT c.id AS conversation_id, c.last_message_id, c.last_message_at,
                    u.id AS other_id, u.name AS other_name, u.surname AS other_surname,
                    u.avatar AS other_avatar,
                    m.body AS last_body, m.sender_id AS last_sender, m.created_at AS last_at,
                    (SELECT COUNT(*) FROM messages mm
                      WHERE mm.conversation_id = c.id AND mm.recipient_id = :uidU
                        AND mm.is_read = 0
                        AND mm.del_recipient = 0) AS unread
               FROM conversations c
               JOIN users u ON u.id = IF(c.user_low = :uidL, c.user_high, c.user_low)
          LEFT JOIN messages m ON m.id = c.last_message_id
              WHERE (c.user_low = :uidA OR c.user_high = :uidB)
                AND NOT (c.user_low = :uidH1 AND c.hidden_by_low = 1)
                AND NOT (c.user_high = :uidH2 AND c.hidden_by_high = 1)
              ORDER BY COALESCE(c.last_message_at, c.created_at) DESC, c.id DESC",
            [
                'uidU' => $userId,
                'uidL' => $userId,
                'uidA' => $userId,
                'uidB' => $userId,
                'uidH1' => $userId,
                'uidH2' => $userId,
            ]
        );

        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'conversation_id' => (int)$r['conversation_id'],
                'other_id'        => (int)$r['other_id'],
                'other_name'      => trim(($r['other_name'] ?? '') . ' ' . ($r['other_surname'] ?? '')),
                'other_avatar'    => $r['other_avatar'] ?? '',
                'last_body'       => $r['last_body'] ?? '',
                'last_sender'     => (int)($r['last_sender'] ?? 0),
                'last_at'         => $r['last_at'] ?: ($r['last_message_at'] ?? ''),
                'unread'          => (int)$r['unread'],
            ];
        }

        return $list;
    }

    /**
     * Message thread — excludes messages soft-deleted by the current user.
     */
    public function thread(int $userId, int $otherId, int $limit = self::THREAD_LIMIT): array
    {
        $conversationId = $this->ensureConversation($userId, $otherId);
        $limit = max(1, min(500, $limit));

        $rows = $this->db->fetchAll(
            "SELECT m.id, m.conversation_id, m.sender_id, m.recipient_id, m.body, m.is_read, m.created_at,
                    m.forwarded_from, m.forwarded_by,
                    uf.name AS ff_name, uf.surname AS ff_surname, uf.avatar AS ff_avatar,
                    ub.name AS fb_name, ub.surname AS fb_surname, ub.avatar AS fb_avatar
               FROM messages m
          LEFT JOIN users uf ON uf.id = m.forwarded_from
          LEFT JOIN users ub ON ub.id = m.forwarded_by
              WHERE m.conversation_id = :conv
                AND NOT (m.sender_id = :uid AND m.del_sender = 1)
                AND NOT (m.recipient_id = :uid2 AND m.del_recipient = 1)
              ORDER BY m.id DESC LIMIT {$limit}",
            ['conv' => $conversationId, 'uid' => $userId, 'uid2' => $userId]
        );

        $rows = array_reverse($rows);

        $thread = [];
        foreach ($rows as $r) {
            $item = [
                'id'              => (int)$r['id'],
                'conversation_id' => (int)$r['conversation_id'],
                'sender_id'       => (int)$r['sender_id'],
                'recipient_id'    => (int)$r['recipient_id'],
                'body'            => $r['body'],
                'is_read'         => (bool)$r['is_read'],
                'created_at'      => $r['created_at'],
                'forwarded_from'  => $r['forwarded_from'] ? (int)$r['forwarded_from'] : null,
                'forwarded_by'    => $r['forwarded_by'] ? (int)$r['forwarded_by'] : null,
            ];

            if ($r['forwarded_from']) {
                $item['ff_name']   = trim(($r['ff_name'] ?? '') . ' ' . ($r['ff_surname'] ?? ''));
                $item['ff_avatar'] = $r['ff_avatar'] ?? '';
            }
            if ($r['forwarded_by']) {
                $item['fb_name']   = trim(($r['fb_name'] ?? '') . ' ' . ($r['fb_surname'] ?? ''));
                $item['fb_avatar'] = $r['fb_avatar'] ?? '';
            }

            $thread[] = $item;
        }

        return $thread;
    }

    /**
     * Soft-delete a single message.
     * If $forAll, marks both sides so the message disappears for everyone.
     * Otherwise only marks the current user's side.
     */
    public function deleteMessage(int $messageId, int $userId, bool $forAll = false): ?array
    {
        $msg = $this->db->fetch(
            "SELECT id, sender_id, recipient_id, conversation_id FROM messages WHERE id = :id",
            ['id' => $messageId]
        );
        if (!$msg) return null;

        $isSender = (int)$msg['sender_id'] === $userId;
        $isRecipient = (int)$msg['recipient_id'] === $userId;
        if (!$isSender && !$isRecipient) return null;

        if ($forAll) {
            $this->db->execute(
                "UPDATE messages SET del_sender = 1, del_recipient = 1 WHERE id = :id",
                ['id' => $messageId]
            );
        } else {
            if ($isSender) {
                $this->db->execute("UPDATE messages SET del_sender = 1 WHERE id = :id", ['id' => $messageId]);
            }
            if ($isRecipient) {
                $this->db->execute("UPDATE messages SET del_recipient = 1 WHERE id = :id", ['id' => $messageId]);
            }
        }

        // Hard delete if both sides deleted
        $this->db->execute(
            "DELETE FROM messages WHERE id = :id AND del_sender = 1 AND del_recipient = 1",
            ['id' => $messageId]
        );

        $this->recalcLastMessage((int)$msg['conversation_id']);

        return [
            'id'              => (int)$msg['id'],
            'sender_id'       => (int)$msg['sender_id'],
            'recipient_id'    => (int)$msg['recipient_id'],
            'conversation_id' => (int)$msg['conversation_id'],
        ];
    }

    /**
     * Soft-delete multiple messages.
     * Returns array of deleted message info for realtime push.
     */
    public function deleteMessages(array $messageIds, int $userId, bool $forAll = false): array
    {
        $deleted = [];
        foreach ($messageIds as $id) {
            $info = $this->deleteMessage((int)$id, $userId, $forAll);
            if ($info) $deleted[] = $info;
        }
        return $deleted;
    }

    /**
     * Hide conversation for one user (soft-delete entire dialog).
     */
    public function hideConversation(int $userId, int $otherId): bool
    {
        $conversationId = $this->ensureConversation($userId, $otherId);
        $low = min($userId, $otherId);
        $high = max($userId, $otherId);

        if ($userId === $low) {
            $this->db->execute("UPDATE conversations SET hidden_by_low = 1 WHERE id = :cid", ['cid' => $conversationId]);
        } else {
            $this->db->execute("UPDATE conversations SET hidden_by_high = 1 WHERE id = :cid", ['cid' => $conversationId]);
        }

        // Mark all messages as deleted for this user
        $this->db->execute(
            "UPDATE messages SET del_sender = 1 WHERE conversation_id = :cid AND sender_id = :uid",
            ['cid' => $conversationId, 'uid' => $userId]
        );
        $this->db->execute(
            "UPDATE messages SET del_recipient = 1 WHERE conversation_id = :cid AND recipient_id = :uid",
            ['cid' => $conversationId, 'uid' => $userId]
        );

        // Hard-delete messages where both sides deleted
        $this->db->execute(
            "DELETE FROM messages WHERE conversation_id = :cid AND del_sender = 1 AND del_recipient = 1",
            ['cid' => $conversationId]
        );

        return true;
    }

    /**
     * Delete conversation for everyone — hard-deletes all messages and the conversation.
     */
    public function deleteConversationForAll(int $userId, int $otherId): bool
    {
        $conversationId = $this->ensureConversation($userId, $otherId);

        $this->db->execute("DELETE FROM messages WHERE conversation_id = :cid", ['cid' => $conversationId]);
        $this->db->execute("DELETE FROM conversations WHERE id = :cid", ['cid' => $conversationId]);

        return true;
    }

    /**
     * Recalculate conversations.last_message_id after deletion.
     */
    private function recalcLastMessage(int $conversationId): void
    {
        $row = $this->db->fetch(
            "SELECT id, created_at FROM messages
              WHERE conversation_id = :cid
              ORDER BY id DESC LIMIT 1",
            ['cid' => $conversationId]
        );

        if ($row) {
            $this->db->execute(
                "UPDATE conversations SET last_message_id = :mid, last_message_at = :mat WHERE id = :cid",
                ['mid' => (int)$row['id'], 'mat' => $row['created_at'], 'cid' => $conversationId]
            );
        } else {
            $this->db->execute(
                "UPDATE conversations SET last_message_id = NULL, last_message_at = NULL WHERE id = :cid",
                ['cid' => $conversationId]
            );
        }
    }

    public function markThreadRead(int $userId, int $otherId): void
    {
        $conversationId = $this->ensureConversation($userId, $otherId);

        $this->db->execute(
            "UPDATE messages
                SET is_read = 1
              WHERE conversation_id = :conv AND recipient_id = :uid AND is_read = 0",
            ['conv' => $conversationId, 'uid' => $userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->db->execute(
            "UPDATE messages SET is_read = 1 WHERE recipient_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
    }

    public function countUnread(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM messages WHERE recipient_id = :uid AND is_read = 0 AND del_recipient = 0",
            ['uid' => $userId]
        );

        return (int)($row['c'] ?? 0);
    }
}
