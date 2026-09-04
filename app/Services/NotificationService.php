<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;

/**
 * High-level notification API used by the rest of the app.
 *
 * Each helper persists a notification row (for the bell + badge) and pushes a
 * realtime event to the recipient if they are online. It also assembles a
 * small "author" hint so the client/bell can render without extra queries.
 */
final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notifications,
        private readonly RealtimeService $realtime
    ) {}

    /**
     * Generic notify: persist + realtime push.
     */
    public function notify(
        int $recipientId,
        string $type,
        string $section,
        ?int $actorId,
        array $data = [],
        ?string $link = null,
        bool $realtime = true
    ): void {
        $this->notifications->create($recipientId, $type, $section, $actorId, $data, $link);

        if ($realtime) {
            $this->realtime->push($type, [$recipientId], [
                'id'      => null, // filled below if needed
                'type'    => $type,
                'section' => $section,
                'actor'   => $actorId,
                'link'    => $link,
            ]);
        }
    }

    /**
     * A user sent a friend request to $recipientId.
     */
    public function friendRequest(int $recipientId, int $actorId, array $actor): void
    {
        $this->notify(
            $recipientId,
            'friend.request',
            'friends',
            $actorId,
            [
                'name'    => trim(($actor['name'] ?? '') . ' ' . ($actor['surname'] ?? '')),
                'avatar'  => $actor['avatar'] ?? '',
                'request' => true,
            ],
            '/friends',
            true
        );
    }

    /**
     * $actorId accepted a friend request sent by $recipientId.
     */
    public function friendAccepted(int $recipientId, int $actorId, array $actor): void
    {
        $this->notify(
            $recipientId,
            'friend.accepted',
            'friends',
            $actorId,
            [
                'name'    => trim(($actor['name'] ?? '') . ' ' . ($actor['surname'] ?? '')),
                'avatar'  => $actor['avatar'] ?? '',
                'accepted' => true,
            ],
            '/id' . $actorId,
            true
        );
    }

    /**
     * Called when $userId accepts/declines a request from $otherId: the incoming
     * friend-request notification is marked read, and a realtime event is pushed
     * to $userId so their own bell/sidebar badge refreshes immediately.
     */
    public function resolveIncoming(int $userId, int $otherId): void
    {
        $this->notifications->markIncomingResolved($userId, $otherId);

        if ($this->realtime->enabled()) {
            $this->realtime->push('friend.toggled', [$userId], ['type' => 'friend.toggled']);
        }
    }

    /**
     * A new private message arrived for $recipientId from $senderId.
     * Persists a bell notification (section 'messages') and pushes a realtime
     * `message.event` so the messages page + sidebar badge refresh live.
     */
    public function message(int $recipientId, int $senderId, array $sender): void
    {
        $this->notify(
            $recipientId,
            'message.new',
            'messages',
            $senderId,
            [
                'name'   => trim(($sender['name'] ?? '') . ' ' . ($sender['surname'] ?? '')),
                'avatar' => $sender['avatar'] ?? '',
            ],
            '/messages',
            true
        );

        if ($this->realtime->enabled()) {
            $this->realtime->push('message.event', [$recipientId], ['otherId' => $senderId]);
        }
    }

    /**
     * Called when $userId reads their conversation with $otherId: the matching
     * `message.new` notifications are marked read and a realtime event is pushed
     * to $userId so the bell/sidebar badge refresh immediately.
     */
    public function markThreadResolved(int $userId, int $otherId): void
    {
        $this->notifications->markFrom($userId, $otherId, 'message.new');
        if ($this->realtime->enabled()) {
            $this->realtime->push('message.read', [$userId], ['otherId' => $otherId]);
        }
    }
}
