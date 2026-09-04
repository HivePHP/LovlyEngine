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

use RuntimeException;

/**
 * Secure, re-usable image upload handler.
 *
 * Every image is validated against its REAL decoded content (never the client
 * filename / extension), re-encoded from scratch through GD into a fresh JPEG
 * and stored under a cryptographically random name. Re-encoding neutralises any
 * embedded payload (polyglot images, EXIF/trailer data) and strips metadata.
 *
 * Generic implementation notes for contributors:
 *   - `validateUpload()` answers "is this a safe image at all?"
 *   - `processSquare()` answers "produce a square NxN JPEG for use as an avatar".
 *   - Storage lives under {BASE_PATH}/public/uploads; paths stored in the DB are
 *     web-relative (e.g. /uploads/avatars/ab12cd34...jpg) so they resolve directly
 *     under the document root without any routing/rewrite involvement.
 */
final class ImageService
{
    /** @var array<string,string> mime-type => GD image type constant */
    private const ALLOWED = [
        'image/jpeg' => IMAGETYPE_JPEG,
        'image/png'  => IMAGETYPE_PNG,
        'image/webp' => IMAGETYPE_WEBP,
        'image/gif'  => IMAGETYPE_GIF,
    ];

    private const MAX_BYTES    = 8 * 1024 * 1024; // 8 MB
    private const MAX_WIDTH    = 4000;
    private const MAX_HEIGHT   = 4000;
    private const MAX_PIXELS   = 24_000_000;      // decompression-bomb guard

    private const AVATAR_DIR       = '/uploads/avatars';
    private const AVATAR_SIZE      = 512;
    private const AVATAR_THUMB_SIZE = 150;

    private const PHOTO_DIR         = '/uploads/albums';
    private const PHOTO_MAX_DIM     = 1600;   // long edge in px before re-encode
    private const PHOTO_THUMB_SIZE  = 240;    // square grid thumbnail edge in px

    public static function uploadRoot(): string
    {
        return BASE_PATH . '/public' . self::AVATAR_DIR;
    }

    public static function photoRoot(): string
    {
        return BASE_PATH . '/public' . self::PHOTO_DIR;
    }

    /**
     * Validate an entry from $_FILES.
     *
     * @throws RuntimeException with a human-readable message on any problem.
     */
    public function validateUpload(array $file, int $maxBytes = self::MAX_BYTES): void
    {
        if (!isset($file['error'], $file['tmp_name'], $file['size'])) {
            throw new RuntimeException('Файл не был загружен.');
        }

        $error = (int)$file['error'];
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->uploadErrorMessage($error));
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Недопустимый источник файла.');
        }

        if ((int)$file['size'] <= 0) {
            throw new RuntimeException('Файл пустой.');
        }

        if ((int)$file['size'] > $maxBytes) {
            throw new RuntimeException(
                sprintf('Файл слишком большой (макс. %d МБ).', (int)floor($maxBytes / 1048576))
            );
        }

        $this->assertRealAllowedImage($file['tmp_name']);
    }

    /**
     * Re-decode the actual bytes and confirm this really is a whitelisted
     * image, applying decompression-bomb / dimension guards.
     */
    private function assertRealAllowedImage(string $tmpName): void
    {
        $info = @getimagesize($tmpName);

        if ($info === false || !isset($info[0], $info[1], $info['mime'])) {
            throw new RuntimeException('Файл не является изображением.');
        }

        $type = ($info[2] ?? null);
        $mime = $info['mime'] ?? '';

        if ($type === null || !in_array($type, self::ALLOWED, true)) {
            throw new RuntimeException('Допустимы только JPEG, PNG, WebP или GIF.');
        }

        $width  = $info[0];
        $height = $info[1];

        if ($width <= 0 || $height <= 0) {
            throw new RuntimeException('Недопустимые размеры изображения.');
        }

        if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
            throw new RuntimeException('Изображение слишком большое по размерам.');
        }

        if ($width * $height > self::MAX_PIXELS) {
            throw new RuntimeException('Слишком много пикселей в изображении.');
        }

        // Final authority: can GD actually decode it from its bytes?
        $source = @imagecreatefromstring((string)file_get_contents($tmpName));
        if ($source === false || $source === null) {
            throw new RuntimeException('Не удалось прочитать изображение.');
        }
        imagedestroy($source);
    }

    /**
     * Take an uploaded image, crop it to a centered square and store a fresh,
     * compressed JPEG. Returns both the main avatar and a small thumbnail.
     *
     * @param array $file one $_FILES entry (field "avatar")
     * @param int $size avatar edge length in px
     * @param string|null $previousUrl web-relative url of the avatar to replace
     * @param string|null $folder per-user subdirectory (e.g. "ava_user_23");
     *        when null, a date folder (avatar-YYYY-MM) is used as a fallback
     * @return array{url: string, thumb: string}
     */
    public function processSquare(
        array $file,
        int $size = self::AVATAR_SIZE,
        ?string $previousUrl = null,
        ?string $folder = null
    ): array {
        $this->validateUpload($file);

        $image = @file_get_contents($file['tmp_name']);
        if ($image === false || $image === '') {
            throw new RuntimeException('Не удалось прочитать файл.');
        }

        $source = @imagecreatefromstring($image);
        if ($source === false || $source === null) {
            throw new RuntimeException('Не удалось обработать изображение.');
        }

        // EXIF orientation only ever exists in JPEG/TIFF; reading it from a
        // PNG/WebP/GIF can make @exif_read_data() raise an ErrorException
        // (via the app's error handler) even when suppressed. So only attempt
        // it for real JPEG payloads.
        $mime = (($info = @getimagesizefromstring($image)) ? ($info['mime'] ?? '') : '');

        try {
            if ($mime === 'image/jpeg') {
                $source = $this->applyExifOrientation($source, $file['tmp_name']);
            }

            $w = imagesx($source);
            $h = imagesy($source);
            $side = min($w, $h);

            $square = imagecreatetruecolor($size, $size);

            $srcX = (int)(($w - $side) / 2);
            $srcY = (int)(($h - $side) / 2);

            imagecopyresampled(
                $square, $source,
                0, 0, $srcX, $srcY,
                $size, $size, $side, $side
            );

            imagedestroy($source);

            $this->ensureDir(self::uploadRoot());

            $sub = ($folder !== null && $folder !== '')
                ? '/' . trim($folder)
                : '/avatar-' . date('Y-m');
            $this->ensureDir(self::uploadRoot() . $sub);

            $hash = bin2hex(random_bytes(16));
            $fullPath = self::uploadRoot() . $sub . '/' . $hash . '.jpg';
            $thumbPath = self::uploadRoot() . $sub . '/' . $hash . '_s.jpg';

            if (!imagejpeg($square, $fullPath, 86)) {
                throw new RuntimeException('Не удалось сохранить изображение.');
            }

            // Create a small thumbnail (150×150) from the same square source.
            $thumb = @imagecreatetruecolor(self::AVATAR_THUMB_SIZE, self::AVATAR_THUMB_SIZE);
            if ($thumb) {
                imagecopyresampled(
                    $thumb, $square,
                    0, 0, 0, 0,
                    self::AVATAR_THUMB_SIZE, self::AVATAR_THUMB_SIZE,
                    $size, $size
                );
                imagejpeg($thumb, $thumbPath, 86);
                imagedestroy($thumb);
            }
            imagedestroy($square);

            $url     = self::AVATAR_DIR . $sub . '/' . $hash . '.jpg';
            $thumbUrl = self::AVATAR_DIR . $sub . '/' . $hash . '_s.jpg';

            $this->deletePrevious($previousUrl);

            return ['url' => $url, 'thumb' => $thumbUrl];
        } catch (RuntimeException $e) {
            if (isset($square) && $square !== false) {
                imagedestroy($square);
            }
            throw $e;
        }
    }

    /**
     * Remove the previous avatar file and its thumbnail (but never the
     * shared placeholder).
     */
    public function deletePrevious(?string $url): void
    {
        if ($url === null || $url === '' || !str_starts_with($url, self::AVATAR_DIR . '/')) {
            return;
        }

        $path = BASE_PATH . '/public' . $url;
        $base = realpath(self::uploadRoot());

        if ($base === false) {
            return;
        }

        // Delete the main image.
        $real = realpath($path);
        if ($real !== false && str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            @unlink($real);
        }

        // Delete the corresponding thumbnail (hash_s.jpg).
        $thumbPath = preg_replace('/\.jpg$/', '_s.jpg', $path);
        $thumbReal = realpath($thumbPath);
        if ($thumbReal !== false && str_starts_with($thumbReal, $base . DIRECTORY_SEPARATOR)) {
            @unlink($thumbReal);
        }
    }

    /**
     * Take an uploaded image, re-encode it as a fresh JPEG (long edge downscaled
     * to keep lightboxes lightweight) and store a square cover-crop thumbnail for
     * the grid. Same full security guarantees as processSquare().
     *
     * @param array $file one $_FILES entry (field "photos")
     * @param string|null $folder per-album subdirectory (e.g. "album_7");
     *        when null, a date folder (photo-YYYY-MM) is used as a fallback
     * @return array{url: string, thumb: string}
     */
    public function processPhoto(array $file, ?string $folder = null): array
    {
        $this->validateUpload($file);

        $image = @file_get_contents($file['tmp_name']);
        if ($image === false || $image === '') {
            throw new RuntimeException('Не удалось прочитать файл.');
        }

        $source = @imagecreatefromstring($image);
        if ($source === false || $source === null) {
            throw new RuntimeException('Не удалось обработать изображение.');
        }

        $mime = (($info = @getimagesizefromstring($image)) ? ($info['mime'] ?? '') : '');

        $full  = null;
        $thumb = null;

        try {
            if ($mime === 'image/jpeg') {
                $source = $this->applyExifOrientation($source, $file['tmp_name']);
            }

            $w = imagesx($source);
            $h = imagesy($source);

            $full  = $this->resizeToLongEdge($source, $w, $h, self::PHOTO_MAX_DIM);
            $thumb = $this->makeSquareThumb($source, $w, $h, self::PHOTO_THUMB_SIZE);

            $this->ensureDir(self::photoRoot());

            $sub = ($folder !== null && $folder !== '')
                ? '/' . trim($folder)
                : '/photo-' . date('Y-m');
            $this->ensureDir(self::photoRoot() . $sub);

            $hash = bin2hex(random_bytes(16));
            $fullPath  = self::photoRoot() . $sub . '/' . $hash . '.jpg';
            $thumbPath = self::photoRoot() . $sub . '/' . $hash . '_s.jpg';

            if (!imagejpeg($full, $fullPath, 88)) {
                throw new RuntimeException('Не удалось сохранить изображение.');
            }
            imagejpeg($thumb, $thumbPath, 86);

            $url   = self::PHOTO_DIR . $sub . '/' . $hash . '.jpg';
            $thumbUrl = self::PHOTO_DIR . $sub . '/' . $hash . '_s.jpg';

            return ['url' => $url, 'thumb' => $thumbUrl];
        } catch (RuntimeException $e) {
            if ($source !== false) { imagedestroy($source); }
            if ($full  !== null && $full  !== false) { imagedestroy($full); }
            if ($thumb !== null && $thumb !== false) { imagedestroy($thumb); }
            throw $e;
        }
    }

    /**
     * Remove a stored album photo (main + square thumbnail). Refuses paths
     * outside the albums upload root.
     */
    public function deletePhotoFiles(?string $url): void
    {
        if ($url === null || $url === '' || !str_starts_with($url, self::PHOTO_DIR . '/')) {
            return;
        }

        $base = realpath(self::photoRoot());
        if ($base === false) {
            return;
        }

        $path = BASE_PATH . '/public' . $url;
        $real = realpath($path);
        if ($real !== false && str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            @unlink($real);
        }

        $thumbPath = preg_replace('/\.jpg$/', '_s.jpg', $path);
        $thumbReal = realpath($thumbPath);
        if ($thumbReal !== false && str_starts_with($thumbReal, $base . DIRECTORY_SEPARATOR)) {
            @unlink($thumbReal);
        }
    }

    /**
     * Best-effort removal of a whole album folder (all photos deleted in bulk).
     *
     * @param string|null $folderValue web-relative full url of any photo in the folder
     */
    public function deleteAlbumFolder(?string $folderValue): void
    {
        if ($folderValue === null || $folderValue === '' || !str_starts_with($folderValue, self::PHOTO_DIR . '/')) {
            return;
        }

        $dirPart = dirname($folderValue);
        // dirname of "/uploads/albums/album_7/hash.jpg" = "/uploads/albums/album_7"
        $base = realpath(self::photoRoot());
        if ($base === false) {
            return;
        }

        $dirReal = realpath(BASE_PATH . '/public' . $dirPart);
        if ($dirReal !== false
            && $dirReal !== $base
            && str_starts_with($dirReal, $base . DIRECTORY_SEPARATOR)
        ) {
            foreach (glob($dirReal . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($dirReal);
        }
    }

    /**
     * Re-encode an image scaled so its long edge equals $maxDim (no upscale).
     */
    private function resizeToLongEdge($source, int $w, int $h, int $maxDim)
    {
        if ($w <= $maxDim && $h <= $maxDim) {
            $out = imagecreatetruecolor($w, $h);
            imagecopy($out, $source, 0, 0, 0, 0, $w, $h);
            return $out;
        }

        $scale  = $maxDim / max($w, $h);
        $nw     = max(1, (int)round($w * $scale));
        $nh     = max(1, (int)round($h * $scale));

        $out = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($out, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $out;
    }

    /**
     * Create a centered square cover-crop thumbnail of edge $size.
     */
    private function makeSquareThumb($source, int $w, int $h, int $size)
    {
        $side = min($w, $h);
        $srcX = (int)(($w - $side) / 2);
        $srcY = (int)(($h - $side) / 2);

        $thumb = imagecreatetruecolor($size, $size);
        imagecopyresampled(
            $thumb, $source,
            0, 0, $srcX, $srcY,
            $size, $size, $side, $side
        );
        return $thumb;
    }

    private function applyExifOrientation($image, string $tmpName)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($tmpName, 'IFD0');
        } catch (\Throwable $e) {
            return $image;
        }

        $orientation = (int)(is_array($exif) ? ($exif['Orientation'] ?? 1) : 1);

        switch ($orientation) {
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }

        return $image;
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Не удалось создать каталог для файлов.');
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой.',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен частично.',
            UPLOAD_ERR_NO_FILE => 'Файл не выбран.',
            default => 'Ошибка при загрузке файла.',
        };
    }
}
