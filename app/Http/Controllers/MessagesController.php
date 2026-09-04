<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Services\RealtimeService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

final class MessagesController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly MessageRepository $messages,
        private readonly AuthService $auth,
        private readonly NotificationService $notifications,
        private readonly RealtimeService $realtime,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    /**
     * GET /messages — render the two-pane messenger.
     */
    public function index(): void
    {
        $me = (int)$this->auth->user()['id'];

        $openTo = null;
        $to = (int)($_GET['to'] ?? 0);
        if ($to > 0 && $to !== $me && $this->users->findById($to)) {
            $openTo = $to;
        }

        $this->assets->usePage('messages');

        $this->response->html($this->view->render('messages/messages', [
            'title'         => 'Сообщения',
            'viewer_id'     => $me,
            'open_to'       => $openTo,
            'conversations' => $this->messages->conversations($me),
            'unread_total'  => $this->messages->countUnread($me),
        ]));
    }

    /**
     * GET /api/messages/conversations
     */
    public function conversations(): void
    {
        $me = (int)$this->auth->user()['id'];

        $this->response->json([
            'status'        => 'ok',
            'conversations' => $this->messages->conversations($me),
            'unread'        => $this->messages->countUnread($me),
        ]);
    }

    /**
     * GET /api/messages/unread
     */
    public function unread(): void
    {
        $me = (int)$this->auth->user()['id'];

        $this->response->json([
            'status' => 'ok',
            'unread' => $this->messages->countUnread($me),
        ]);
    }

    /**
     * GET /api/messages/{otherId} — fetch the thread and mark it read.
     */
    public function thread(int $otherId): void
    {
        $this->guardTarget($otherId);
        $me = (int)$this->auth->user()['id'];

        $this->messages->markThreadRead($me, $otherId);
        $this->notifications->markThreadResolved($me, $otherId);

        $this->response->json([
            'status'     => 'ok',
            'thread'     => $this->messages->thread($me, $otherId),
            'unread'     => $this->messages->countUnread($me),
            'other_id'   => $otherId,
            'other_name' => $this->profileName($otherId),
        ]);
    }

    /**
     * POST /api/messages/{otherId}/send
     */
    public function send(int $otherId): void
    {
        $this->guardTarget($otherId);
        $me = (int)$this->auth->user()['id'];

        if ($me === $otherId) {
            $this->response->json(['status' => 'error', 'message' => 'Нельзя написать самому себе.'], 422);
            return;
        }

        $input = $this->request->json();
        $body  = trim((string)($input['body'] ?? ''));

        if ($body === '') {
            $this->response->json(['status' => 'error', 'message' => 'Сообщение не может быть пустым.'], 422);
            return;
        }
        if (mb_strlen($body) > 4000) {
            $this->response->json(['status' => 'error', 'message' => 'Сообщение слишком длинное.'], 422);
            return;
        }

        $message = $this->messages->send(
            $me,
            $otherId,
            $body,
            $input['forwarded_from'] ?? null,
            $input['forwarded_by'] ?? null
        );

        // Notify the recipient (bell + realtime) so the badge/messenger update live.
        $sender = $this->auth->user();
        if ($sender) {
            $this->notifications->message($otherId, $me, $sender);
        }

        $this->response->json([
            'status'  => 'ok',
            'message' => $message,
            'unread'  => $this->messages->countUnread($me),
        ]);
    }

    private function profileName(int $userId): string
    {
        $u = $this->users->findById($userId);
        return trim(($u['name'] ?? '') . ' ' . ($u['surname'] ?? ''));
    }

    private function guardTarget(int $id): void
    {
        if (!$this->users->findById($id)) {
            $this->response->json(['status' => 'error', 'message' => 'Пользователь не найден.'], 404);
            exit;
        }
    }

    /**
     * POST /api/messages/{messageId}/delete — delete a single message.
     * Body: { "mode": "for_me" | "for_all" }
     */
    public function deleteMessage(int $messageId): void
    {
        $me = (int)$this->auth->user()['id'];
        $input = $this->request->json();
        $mode = $input['mode'] ?? 'for_me';
        $forAll = ($mode === 'for_all');

        $info = $this->messages->deleteMessage((int)$messageId, $me, $forAll);

        if ($info) {
            $otherId = ($info['sender_id'] === $me) ? $info['recipient_id'] : $info['sender_id'];
            $targets = $forAll ? [$me, $otherId] : [$me];
            $this->realtime->push('message.deleted', $targets, [
                'messageId' => $info['id'],
                'conversationId' => $info['conversation_id'],
            ]);
        }

        $this->response->json([
            'status' => $info ? 'ok' : 'error',
            'message_id' => $messageId,
        ]);
    }

    /**
     * POST /api/messages/delete-batch — delete multiple messages.
     * Body: { "ids": [1,2,3], "mode": "for_me" | "for_all" }
     */
    public function deleteBatch(): void
    {
        $me = (int)$this->auth->user()['id'];
        $input = $this->request->json();
        $ids = $input['ids'] ?? [];
        $mode = $input['mode'] ?? 'for_me';
        $forAll = ($mode === 'for_all');

        if (!is_array($ids) || empty($ids) || count($ids) > 100) {
            $this->response->json(['status' => 'error', 'message' => 'Invalid ids'], 422);
            return;
        }

        $deleted = $this->messages->deleteMessages(array_map('intval', $ids), $me, $forAll);

        // Push realtime only to affected users
        if ($forAll) {
            $userIds = [$me];
            foreach ($deleted as $msg) {
                $userIds[] = $msg['sender_id'];
                $userIds[] = $msg['recipient_id'];
            }
            $userIds = array_unique($userIds);
            if (count($userIds) > 1) {
                $this->realtime->push('message.deleted', array_values($userIds), [
                    'messageIds' => array_map(fn($m) => $m['id'], $deleted),
                ]);
            }
        } else {
            $this->realtime->push('message.deleted', [$me], [
                'messageIds' => array_map(fn($m) => $m['id'], $deleted),
            ]);
        }

        $this->response->json([
            'status' => 'ok',
            'deleted' => count($deleted),
        ]);
    }

    /**
     * POST /api/messages/{otherId}/delete-conversation — hide/delete entire conversation.
     * Body: { "mode": "for_me" | "for_all" }
     */
    public function deleteConversation(int $otherId): void
    {
        $me = (int)$this->auth->user()['id'];
        $input = $this->request->json();
        $mode = $input['mode'] ?? 'for_me';

        if ($mode === 'for_all') {
            $this->messages->deleteConversationForAll($me, $otherId);
        } else {
            $this->messages->hideConversation($me, $otherId);
        }

        $this->realtime->push('conversation.deleted', [$me, $otherId], [
            'otherId' => $otherId,
        ]);

        $this->response->json(['status' => 'ok']);
    }
}
