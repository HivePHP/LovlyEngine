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

use App\Repositories\FriendsRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Services\OnlineService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

final class FriendsController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly FriendsRepository $friends,
        private readonly AuthService $auth,
        private readonly NotificationService $notifications,
        private readonly OnlineService $online,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    public function index(): void
    {
        $user = $this->auth->user();
        $userId = (int)$user['id'];

        $this->assets->usePage('friends');

        $friends   = $this->friends->friends($userId);
        $incoming  = $this->friends->incomingRequests($userId);
        $outgoing  = $this->friends->outgoingRequests($userId);

        // Mutual-friend counts for the confirmed friends list.
        foreach ($friends as &$f) {
            $f['mutual_count'] = $this->friends->countCommonFriends($userId, (int)$f['id']);
        }
        unset($f);

        // Online statuses for all friend user IDs.
        $allUserIds = array_merge(
            array_map(fn($f) => (int)$f['id'], $friends),
            array_map(fn($f) => (int)$f['friend_user_id'], $incoming),
            array_map(fn($f) => (int)$f['friend_user_id'], $outgoing),
        );
        $onlineStatuses = $this->online->getOnlineStatuses($allUserIds);

        $onlineCount = 0;

        // Add last_seen_text and is_online to each friend row.
        foreach ($friends as &$f) {
            $uid = (int)$f['id'];
            $f['is_online'] = $onlineStatuses[$uid] ?? false;
            $f['last_seen_text'] = $f['is_online'] ? null : $this->online->formatLastSeen($f['last_seen_at'] ?? null);
            if ($f['is_online']) {
                $onlineCount++;
            }
        }
        unset($f);

        foreach ($incoming as &$r) {
            $uid = (int)$r['friend_user_id'];
            $r['is_online'] = $onlineStatuses[$uid] ?? false;
            $r['last_seen_text'] = $r['is_online'] ? null : $this->online->formatLastSeen($r['last_seen_at'] ?? null);
        }
        unset($r);

        foreach ($outgoing as &$r) {
            $uid = (int)$r['friend_user_id'];
            $r['is_online'] = $onlineStatuses[$uid] ?? false;
            $r['last_seen_text'] = $r['is_online'] ? null : $this->online->formatLastSeen($r['last_seen_at'] ?? null);
        }
        unset($r);

        // Possible friends for the right sidebar.
        $possible = $this->friends->possibleFriends($userId, 10);
        $possibleIds = array_map(fn($p) => (int)$p['id'], $possible);
        if ($possibleIds) {
            $possibleOnline = $this->online->getOnlineStatuses($possibleIds);
            foreach ($possible as &$p) {
                $uid = (int)$p['id'];
                $p['is_online'] = $possibleOnline[$uid] ?? false;
            }
            unset($p);
        }

        $this->response->html($this->view->render('friends/friends', [
            'title'          => 'Друзья',
            'viewer_id'      => $userId,
            'friends'        => $friends,
            'friend_count'   => count($friends),
            'online_count'   => $onlineCount,
            'incoming'       => $incoming,
            'incoming_count' => count($incoming),
            'outgoing'       => $outgoing,
            'outgoing_count' => count($outgoing),
            'possible'       => $possible,
        ]));
    }

    public function add(int $id): void
    {
        $this->guardTarget((int)$id);
        $self = (int)$this->auth->user()['id'];

        if ($self === (int)$id) {
            $this->response->json(['status' => 'error', 'message' => 'Нельзя добавить себя в друзья.'], 422);
            return;
        }

        $before = $this->friends->relation($self, (int)$id);
        $relation = $this->friends->add($self, (int)$id);

        if ($relation === 'outgoing') {
            // A brand new outgoing request: notify the recipient.
            $me = $this->auth->user();
            $this->notifications->friendRequest((int)$id, $self, $me);
        } elseif ($relation === 'friends' && $before === 'incoming') {
            // We just accepted an inbound request -> notify its sender.
            $me = $this->auth->user();
            $this->notifications->friendAccepted((int)$id, $self, $me);

            // Our own incoming-request notification is now resolved too.
            $this->notifications->resolveIncoming($self, (int)$id);
        }

        $this->response->json([
            'status'   => 'ok',
            'relation' => $relation,
        ]);
    }

    public function accept(int $id): void
    {
        $this->guardTarget((int)$id);
        $self = (int)$this->auth->user()['id'];

        if (!$this->friends->accept($self, (int)$id)) {
            $this->response->json(['status' => 'error', 'message' => 'Заявка не найдена.'], 404);
            return;
        }

        // We accepted $id's request -> notify $id that we are now friends.
        $me = $this->auth->user();
        $this->notifications->friendAccepted((int)$id, $self, $me);

        // Our own incoming-request notification for this user is now resolved,
        // so our "Друзья" badge no longer counts it.
        $this->notifications->resolveIncoming($self, (int)$id);

        $this->response->json([
            'status'   => 'ok',
            'relation' => 'friends',
        ]);
    }

    public function decline(int $id): void
    {
        $this->guardTarget((int)$id);
        $self = (int)$this->auth->user()['id'];

        $this->friends->decline($self, (int)$id);

        // Our own incoming-request notification for this user is now resolved.
        $this->notifications->resolveIncoming($self, (int)$id);

        $this->response->json([
            'status'   => 'ok',
            'relation' => 'none',
        ]);
    }

    public function remove(int $id): void
    {
        $this->guardTarget((int)$id);
        $self = (int)$this->auth->user()['id'];

        $this->friends->remove($self, (int)$id);

        $this->response->json([
            'status'   => 'ok',
            'relation' => 'none',
        ]);
    }

    private function guardTarget(int $id): void
    {
        $exists = $this->users->findById($id);

        if (!$exists) {
            $this->response->json(['status' => 'error', 'message' => 'Пользователь не найден.'], 404);
            exit;
        }
    }
}
