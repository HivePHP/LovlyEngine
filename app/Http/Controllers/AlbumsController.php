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
use HivePHP\Validation\Validator;
use HivePHP\View\View;
use RuntimeException;

final class AlbumsController extends Controller
{
    private const TITLE_MAX = 255;
    private const DESC_MAX  = 5000;

    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly AuthService $auth,
        private readonly AlbumRepository $albums,
        private readonly PhotoRepository $photos,
        private readonly ImageService $images,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    /* ===================== */
    /* Pages                 */
    /* ===================== */

    /**
     * Albums list page for a user: /albums/id{id}
     */
    public function index(int $id): void
    {
        $user = $this->users->findProfileById($id);
        if (!$user) {
            $this->response->html('404 Not Found', 404);
            return;
        }

        $current = $this->auth->user();
        $isOwner = $current && (int)$current['id'] === (int)$id;

        $this->assets->usePage('albums');

        $albums = $this->albums->allForUser((int)$id);
        foreach ($albums as &$album) {
            $album['photo_text'] = $this->photoText((int)$album['photo_count']);
        }
        unset($album);

        $this->response->html($this->view->render('albums/albums', [
            'title'     => 'Фотографии' . ' — ' . trim($user['name'] . ' ' . $user['surname']),
            'user_id'   => (int)$user['id'],
            'owner_name'=> trim($user['name'] . ' ' . $user['surname']),
            'is_owner'  => $isOwner,
            'albums'    => $albums,
        ]));
    }

    /**
     * Single album page (separate page): /album/{id}
     */
    public function show(int $id): void
    {
        $album = $this->albums->findById($id);
        if (!$album) {
            $this->response->html('404 Not Found', 404);
            return;
        }

        $current = $this->auth->user();
        $isOwner = $current && (int)$current['id'] === (int)$album['user_id'];

        $this->assets->usePage('albums');

        $this->response->html($this->view->render('albums/album', [
            'title'     => $album['title'] . ' — Фотографии',
            'album'     => $album,
            'photos'    => $this->photos->allForAlbum($id),
            'is_owner'  => $isOwner,
            'user_id'   => $album['user_id'],
        ]));
    }

    /* ===================== */
    /* API: create           */
    /* ===================== */

    public function create(): void
    {
        $user = $this->auth->user();

        $input = $this->request->json();

        $validator = Validator::make($input, [
            'title'       => 'required|string|max:' . self::TITLE_MAX,
            'description' => 'nullable|string|max:' . self::DESC_MAX,
        ])->validate();

        if ($validator->fails()) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
            return;
        }

        $data = $validator->clean();

        $title       = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($title === '') {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Введите название альбома.',
            ], 422);
            return;
        }

        $sort = $this->albums->maxSortOrder((int)$user['id']) + 1;
        $id   = $this->albums->create((int)$user['id'], $title, $description, $sort);

        $this->response->json([
            'status' => 'ok',
            'id'     => $id,
            'url'    => '/album/' . $id,
        ]);
    }

    /* ===================== */
    /* API: reorder          */
    /* ===================== */

    public function reorder(): void
    {
        $user = $this->auth->user();

        $input = $this->request->json();
        $ids   = $input['ids'] ?? null;

        if (!is_array($ids)) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Некорректный список альбомов.',
            ], 422);
            return;
        }

        $ids = array_values(array_filter(array_map(
            static fn($v) => (int)$v,
            $ids
        )));

        $this->albums->reorder((int)$user['id'], $ids);

        $this->response->json(['status' => 'ok']);
    }

    /* ===================== */
    /* API: reorder photos   */
    /* ===================== */

    public function reorderPhotos(int $id): void
    {
        $user  = $this->auth->user();
        $album = $this->albums->findById($id);

        if (!$album || (int)$album['user_id'] !== (int)$user['id']) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Альбом не найден.',
            ], 404);
            return;
        }

        $input = $this->request->json();
        $ids   = $input['ids'] ?? null;

        if (!is_array($ids)) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Некорректный список фотографий.',
            ], 422);
            return;
        }

        $ids = array_values(array_filter(array_map(
            static fn($v) => (int)$v,
            $ids
        )));

        $this->photos->reorder((int)$id, $ids);

        $this->response->json(['status' => 'ok']);
    }

    /* ===================== */
    /* API: upload photos    */
    /* ===================== */

    public function upload(int $id): void
    {
        $user  = $this->auth->user();
        $album = $this->albums->findById($id);
        $albumId = $id;

        if (!$album || (int)$album['user_id'] !== (int)$user['id']) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Альбом не найден.',
            ], 404);
            return;
        }

        if ((int)$album['is_protected'] === 1) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'В этот альбом фотографии загружаются только при смене аватарки.',
            ], 403);
            return;
        }

        if (!$this->request->hasFile('photos')) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Файлы не выбраны.',
            ], 422);
            return;
        }

        $files = $this->normalizeUploads($this->request->file('photos'));
        if (!$files) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Файлы не выбраны.',
            ], 422);
            return;
        }

        $folder = 'album_' . $albumId;
        $saved  = [];

        try {
            foreach ($files as $file) {
                // Skip "no file" entries that may appear in multi-file arrays.
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $result = $this->images->processPhoto($file, $folder);

                $this->photos->create($albumId, (int)$user['id'], $result['url'], $result['thumb']);
                $saved[] = $result;
            }
        } catch (RuntimeException $e) {
            $this->response->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
            return;
        }

        if (!$saved) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Не удалось загрузить ни одного файла.',
            ], 422);
            return;
        }

        $photos = $this->photos->allForAlbum($albumId);

        $this->response->json([
            'status'       => 'ok',
            'uploaded'     => count($saved),
            'photo_count'  => count($photos),
            'cover'        => $photos[0]['path'] ?? null,
        ]);
    }

    /* ===================== */
    /* API: delete photo     */
    /* ===================== */

    public function deletePhoto(int $id): void
    {
        $user  = $this->auth->user();
        $photo = $this->photos->findById($id);
        $photoId = $id;

        if (!$photo || (int)$photo['user_id'] !== (int)$user['id']) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Фотография не найдена.',
            ], 404);
            return;
        }

        $albumId = (int)$photo['album_id'];
        $album   = $this->albums->findById($albumId);
        $isProtected = $album !== null && (int)$album['is_protected'] === 1;

        $this->images->deletePhotoFiles($photo['path']);
        $this->photos->delete($photoId);

        // In the protected page album each photo also represents an avatar crop.
        // Removing the current one resets the avatar to the previous (or clears
        // it); removing an older one just retires its orphaned crop on disk.
        $newAvatar = null;
        if ($isProtected) {
            $newAvatar = $this->retireAvatarCrop((int)$user['id'], $photo, $albumId);
        }

        $photos  = $this->photos->allForAlbum($albumId);

        $this->response->json([
            'status'       => 'ok',
            'photo_count'  => count($photos),
            'cover'        => $photos[0]['path'] ?? null,
            'album_url'    => '/album/' . $albumId,
            'avatar'       => $newAvatar !== null ? ['url' => $newAvatar] : null,
        ]);
    }

    /* ===================== */
    /* API: delete album     */
    /* ===================== */

    public function destroy(int $id): void
    {
        $user  = $this->auth->user();
        $album = $this->albums->findById($id);

        if (!$album || (int)$album['user_id'] !== (int)$user['id']) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Альбом не найден.',
            ], 404);
            return;
        }

        if ((int)$album['is_protected'] === 1) {
            $this->response->json([
                'status'  => 'error',
                'message' => 'Этот альбом нельзя удалить.',
            ], 403);
            return;
        }

        // Remove all photo files + the album folder from disk.
        $photos = $this->photos->allForAlbum($id);
        foreach ($photos as $photo) {
            $this->images->deletePhotoFiles($photo['path']);
        }
        $this->images->deleteAlbumFolder($photos[0]['path'] ?? null);

        // Delete the album row (photo rows are removed via FK CASCADE).
        $this->albums->delete($id);

        $this->response->json([
            'status' => 'ok',
            'url'    => '/albums/id' . (int)$user['id'],
        ]);
    }

    /* ===================== */
    /* Helpers               */
    /* ===================== */

    /**
     * After removing an avatar-original photo from the protected page album,
     * clean up the avatar crop it referenced and, when the removed photo was the
     * current avatar, restore the previous avatar (or clear it). Returns the
     * resulting avatar url (null when the avatar was reset to the default).
     *
     * @param array<string, mixed> $deletedPhoto
     */
    private function retireAvatarCrop(int $userId, array $deletedPhoto, int $albumId): ?string
    {
        $cropUrl       = (string)($deletedPhoto['avatar_url'] ?? '');
        $currentAvatar = (string)($this->users->findById($userId)['avatar'] ?? '');

        // No crop reference: nothing else to do (avatar untouched).
        if ($cropUrl === '') {
            return $currentAvatar === '' ? null : $currentAvatar;
        }

        // Not the current avatar: it was an older photo, so its crop is now
        // orphaned — remove the files but keep the avatar as-is.
        if ($cropUrl !== $currentAvatar) {
            $this->images->deletePrevious($cropUrl);
            return $currentAvatar === '' ? null : $currentAvatar;
        }

        // It IS the current avatar: fall back to the previous one (the newest
        // remaining original uploaded before this one), or clear the avatar.
        $deletedSort = (int)$deletedPhoto['sort_order'];
        $previous    = null;

        foreach ($this->photos->avatarPhotos($albumId) as $ap) {
            if ((int)$ap['id'] === (int)$deletedPhoto['id']) {
                continue;
            }
            if ((int)$ap['sort_order'] < $deletedSort) {
                $previous = $ap['avatar_url'];
            }
        }

        // Retire the crop of the dropped avatar and point at the previous one.
        $this->images->deletePrevious($cropUrl);
        $this->users->updateAvatar($userId, $previous);

        return $previous;
    }

    private function photoText(int $count): string
    {
        if ($count === 0) {
            return 'Нет фотографий';
        }

        $mod10 = $count % 10;
        $mod100 = $count % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return $count . ' фотография';
        }

        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
            return $count . ' фотографии';
        }

        return $count . ' фотографий';
    }

    /**
     * Normalize $_FILES['photos'] (single entry or multi-file array) into a
     * list of per-file entries suitable for ImageService::validateUpload().
     *
     * @return array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    private function normalizeUploads(array $entry): array
    {
        if (!is_array($entry['name'] ?? null)) {
            return [$entry];
        }

        $files = [];
        $count = count($entry['name']);

        for ($i = 0; $i < $count; $i++) {
            $files[] = [
                'name'     => $entry['name'][$i] ?? '',
                'type'     => $entry['type'][$i] ?? '',
                'tmp_name' => $entry['tmp_name'][$i] ?? '',
                'error'    => (int)($entry['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int)($entry['size'][$i] ?? 0),
            ];
        }

        return $files;
    }
}
