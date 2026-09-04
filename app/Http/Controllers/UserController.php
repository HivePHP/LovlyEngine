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
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;
use JsonException;

final class UserController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
    ];

    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly PhotoRepository $photos,
        private readonly FriendsRepository $friends,
        private readonly AuthService $auth,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    /**
     * @throws JsonException
     */
    public function show(int $id): void
    {
        $user = $this->users->findProfileById($id);

        if (!$user) {
            $this->response->json(['status' => 'error', 'message' => 'User not found'], 404);
            return;
        }

        $month = (int)$user['month'];
        $day   = (int)$user['day'];
        $year  = (int)$user['year'];
        $birthday = $day . ' ' . (self::MONTH_NAMES[$month] ?? '') . ' ' . $year . ' г.';

        $this->assets->usePage('profile');

        $recentPhotos = $this->photos->recentForUser((int)$user['id'], 5);
        $photoCount   = $this->photos->countForUser((int)$user['id']);

        $current    = $this->auth->user();
        $isOwner    = $current !== null && (int)$current['id'] === (int)$user['id'];
        $avatarOriginal = '';
        if ($isOwner && $avatar = (string)($user['avatar'] ?? '')) {
            $original = $this->photos->findOriginalByAvatar((int)$user['id'], $avatar);
            $avatarOriginal = (string)($original['path'] ?? '');
        }

        // Viewer ↔ profile-owner friendship state.
        $friendRelation = 'none';
        $commonFriends  = [];
        if ($current !== null && !$isOwner) {
            $viewerId      = (int)$current['id'];
            $friendRelation = $this->friends->relation($viewerId, (int)$user['id']);
            $commonFriends  = $this->friends->commonFriends($viewerId, (int)$user['id']);
        }

        $this->response->html($this->view->render('profile/profile', [
            'title'           => trim($user['name'] . ' ' . $user['surname']),
            'name'            => $user['name'],
            'surname'         => $user['surname'],
            'avatar'          => $user['avatar'] ?? '',
            'avatar_original' => $avatarOriginal,
            'status'          => $user['status'] ?? '',
            'sex'             => $user['sex'],
            'city'            => $user['city'],
            'country'         => $user['country'],
            'birthday'        => $birthday,
            'user_id'         => $user['id'],
            'about'           => $user['about'] ?? '',
            'interests'       => $user['interests'] ?? '',
            'favorite_films'  => $user['favorite_films'] ?? '',
            'photo_count'     => $photoCount,
            'recent_photos'   => $recentPhotos,
            'albums_url'      => '/albums/id' . (int)$user['id'],
            'viewer_id'       => $current ? (int)$current['id'] : null,
            'friend_relation' => $friendRelation,
            'friend_count'    => $this->friends->countFriends((int)$user['id']),
            'common_friends'  => $commonFriends,
        ]));
    }
}
