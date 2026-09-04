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
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\ImageService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;
use RuntimeException;

final class AvatarUploadController extends Controller
{
    /** System album that keeps the original (un-cropped) avatar images. */
    private const AVATAR_ALBUM_TITLE = 'Фотографии с моей страницы';

    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly AuthService $auth,
        private readonly ImageService $images,
        private readonly AlbumRepository $albums,
        private readonly PhotoRepository $photos,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    public function save(): void
    {
        $user = $this->auth->user();

        if (!$this->request->hasFile('avatar')) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Файл не выбран.',
            ], 422);
            return;
        }

        $cropped  = $this->request->file('avatar');
        $original = $this->request->hasFile('original') ? $this->request->file('original') : null;
        $reCrop   = ($_POST['reCrop'] ?? '') === '1';
        $currentAvatar = (string)($user['avatar'] ?? '');

        try {
            $userId = (int)$user['id'];

            // Only the owner may change their own avatar; auth guarantees $user.
            // NOTE: previous crop files are intentionally kept on disk — each one
            // is referenced by its original stored in the protected page album and
            // is only removed when that album photo is deleted (avatar reset).
            $result = $this->images->processSquare(
                $cropped,
                folder: 'ava_user_' . $userId
            );

            $this->users->updateAvatar($userId, $result['url']);

            if ($reCrop) {
                // Re-crop of the current picture: repoint the existing album
                // photo to the new crop rather than storing a duplicate original.
                $this->reCropAvatar($userId, $currentAvatar, $result['url']);
            } else {
                // Keep the original (un-cropped) image in the protected page album.
                $this->storeOriginalAvatar($userId, $cropped, $original, $result['url']);
            }

            $this->response->json([
                'status' => 'ok',
                'url'    => $result['url'],
                'thumb'  => $result['thumb'],
            ]);
        } catch (RuntimeException $e) {
            $this->response->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reset the avatar to the previous one (or the default) and remove the
     * current avatar's photo from the protected page album. Used by "Удалить
     * аватарку". Returns the resulting avatar url (null = default).
     */
    public function delete(): void
    {
        $user     = $this->auth->user();
        $userId   = (int)$user['id'];
        $current  = (string)($user['avatar'] ?? '');
        $album    = $this->albums->findProtectedByUserId($userId);

        $newAvatar = null;

        if ($album !== null && $current !== '') {
            $albumId = (int)$album['id'];
            $photo   = null;

            foreach ($this->photos->avatarPhotos($albumId) as $ap) {
                if ((string)($ap['avatar_url'] ?? '') === $current) {
                    $photo = $ap;
                    break;
                }
            }

            if ($photo) {
                // Remove the current avatar's photo and fall back to the
                // previous one (or clear the avatar when none remains).
                $this->images->deletePhotoFiles($photo['path']);
                $this->photos->delete((int)$photo['id']);
                $newAvatar = $this->resetAvatarFromAlbum($userId, $current, $albumId);
            }
        }

        if ($newAvatar === null) {
            $this->users->updateAvatar($userId, null);
        }

        $this->response->json([
            'status' => 'ok',
            'avatar' => $newAvatar === null ? null : ['url' => $newAvatar],
        ]);
    }

    /**
     * Save the original (un-cropped) avatar into the protected "page" album.
     * The album is created on first use and can never be deleted.
     */
    private function storeOriginalAvatar(int $userId, array $original, ?array $fallbackFile, string $avatarUrl): void
    {
        $album = $this->albums->findProtectedByUserId($userId);

        if (!$album) {
            $sort = $this->albums->maxSortOrder($userId) + 1;
            $id   = $this->albums->create(
                $userId,
                self::AVATAR_ALBUM_TITLE,
                '',
                $sort,
                isProtected: true
            );
        } else {
            $id = (int)$album['id'];
        }

        // Prefer the client-sent original; fall back to the cropped file so the
        // album entry is created even on partial clients.
        $file = $original ?? $fallbackFile;
        if ($file === null) {
            return;
        }

        $folder  = 'album_' . $id;
        $stored  = $this->images->processPhoto($file, $folder);

        $this->photos->create($id, $userId, $stored['url'], $stored['thumb'], $avatarUrl);
    }

    /**
     * Re-crop of the current avatar: repoint the existing album photo (that
     * originally produced the avatar crop) at the new crop url instead of
     * storing a second original. If no matching photo is found, fall back to
     * storing a new original so the album entry still exists.
     */
    private function reCropAvatar(int $userId, string $currentAvatar, string $newCropUrl): void
    {
        $album = $this->albums->findProtectedByUserId($userId);

        if ($album !== null) {
            foreach ($this->photos->avatarPhotos((int)$album['id']) as $ap) {
                if ((string)($ap['avatar_url'] ?? '') === $currentAvatar) {
                    $this->photos->updateAvatarUrl((int)$ap['id'], $newCropUrl);
                    return;
                }
            }
        }
    }

    /**
     * After removing the current avatar's photo from the protected album, fall
     * back to the newest remaining original (uploaded before the removed one),
     * retiring the dropped crop on disk. Returns the new avatar url (null when
     * it resets to the default).
     */
    private function resetAvatarFromAlbum(int $userId, string $currentAvatar, int $albumId): ?string
    {
        $deletedSort = PHP_INT_MAX;
        $newestPrev  = null;

        foreach ($this->photos->avatarPhotos($albumId) as $ap) {
            $apAvatar = (string)($ap['avatar_url'] ?? '');
            if ($apAvatar === $currentAvatar) {
                $deletedSort = (int)$ap['sort_order'];
                continue;
            }
            if ((int)$ap['sort_order'] < $deletedSort) {
                $newestPrev = $apAvatar;
            }
        }

        $this->images->deletePrevious($currentAvatar);
        $this->users->updateAvatar($userId, $newestPrev);

        return $newestPrev === '' ? null : $newestPrev;
    }
}
