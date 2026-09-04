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

        $this->response->html($this->view->render('friends/friends', [
            'title'          => 'Друзья',
            'viewer_id'      => $userId,
            'friends'        => $friends,
            'friend_count'   => count($friends),
            'incoming'       => $incoming,
            'incoming_count' => count($incoming),
            'outgoing'       => $outgoing,
            'outgoing_count' => count($outgoing),
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

        $relation = $this->friends->add($self, (int)$id);

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
