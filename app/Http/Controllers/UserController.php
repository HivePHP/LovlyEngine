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

use App\Repositories\AlbumRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\FriendsRepository;
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\OnlineService;
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
        private readonly AlbumRepository $albums,
        private readonly DocumentRepository $documents,
        private readonly AuthService $auth,
        private readonly OnlineService $online,
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

        // Profile blocks: friends, albums, documents.
        $profileUserId  = (int)$user['id'];
        $profileFriends = array_slice($this->friends->friends($profileUserId), 0, 6);
        $profileAlbums  = array_slice($this->albums->allForUser($profileUserId), 0, 2);
        $profileDocs    = $this->documents->recentForUser($profileUserId, 5);

        // Online status.
        $isOnline      = $this->online->isOnline($profileUserId);
        $lastSeenText  = $isOnline ? null : $this->online->formatLastSeen($user['last_seen_at'] ?? null);

        // Online statuses for sidebar friends.
        $friendIds = array_map(fn($f) => (int)$f['id'], $profileFriends);
        $sidebarOnline = $this->online->getOnlineStatuses($friendIds);
        foreach ($profileFriends as &$pf) {
            $pf['is_online'] = $sidebarOnline[(int)$pf['id']] ?? false;
        }
        unset($pf);

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
            'albums_url'      => '/albums/id' . $profileUserId,
            'viewer_id'       => $current ? (int)$current['id'] : null,
            'friend_relation' => $friendRelation,
            'friend_count'    => $this->friends->countFriends($profileUserId),
            'common_friends'  => $commonFriends,
            'profile_friends' => $profileFriends,
            'profile_albums'  => $profileAlbums,
            'profile_docs'    => $profileDocs,
            'profile_friends_total' => $this->friends->countFriends($profileUserId),
            'profile_albums_total'  => count($this->albums->allForUser($profileUserId)),
            'profile_docs_total'    => $this->documents->countForUser($profileUserId),
            'is_online'             => $isOnline,
            'last_seen_text'        => $lastSeenText,
        ]));
    }
}
