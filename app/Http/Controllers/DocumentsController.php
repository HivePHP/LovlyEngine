<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\DocumentRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

final class DocumentsController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly DocumentRepository $documents,
        private readonly AuthService $auth,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    public function page(): void
    {
        $userId = (int) $this->auth->user()['id'];

        $this->assets->usePage('documents');

        $docs = $this->documents->allForUser($userId);

        $this->response->html($this->view->render('documents/documents', [
            'title'       => 'Мои документы',
            'viewer_id'   => $userId,
            'documents'   => $docs,
            'doc_count'   => count($docs),
            'total_size'  => $this->documents->totalSizeForUser($userId),
            'is_public'   => false,
            'owner_id'    => $userId,
            'owner_name'  => '',
        ]));
    }

    public function userPage(int $id): void
    {
        $user = $this->users->findProfileById($id);
        if (!$user) {
            $this->response->html('404 Not Found', 404);
            return;
        }

        $current = $this->auth->user();
        $viewerId = $current ? (int)$current['id'] : null;

        $this->assets->usePage('documents');

        $docs = $this->documents->allForUser($id);

        $this->response->html($this->view->render('documents/documents', [
            'title'       => 'Документы — ' . trim($user['name'] . ' ' . $user['surname']),
            'viewer_id'   => $viewerId,
            'documents'   => $docs,
            'doc_count'   => count($docs),
            'total_size'  => $this->documents->totalSizeForUser($id),
            'is_public'   => true,
            'owner_id'    => $id,
            'owner_name'  => trim($user['name'] . ' ' . $user['surname']),
        ]));
    }

    public function upload(): void
    {
        $userId = (int) $this->auth->user()['id'];

        $raw = $this->request->file('files');
        if (!$raw || !is_array($raw)) {
            $this->response->json(['status' => 'error', 'message' => 'Файлы не загружены.'], 422);
            return;
        }

        $files = $this->normalizeUploads($raw);

        $dir = BASE_PATH . '/public/uploads/documents/' . $userId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $uploaded = 0;
        foreach ($files as $file) {
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                continue;
            }

            $originalName = $file['name'] ?? 'unnamed';
            $tmpPath     = $file['tmp_name'];
            $size        = (int) ($file['size'] ?? 0);
            $type        = $file['type'] ?? 'application/octet-stream';

            if ($size <= 0) {
                continue;
            }

            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = bin2hex(random_bytes(12)) . ($ext ? '.' . $ext : '');
            $destPath = '/uploads/documents/' . $userId . '/' . $safeName;

            if (!move_uploaded_file($tmpPath, $dir . '/' . $safeName)) {
                continue;
            }

            $this->documents->create($userId, $originalName, $type, $size, $destPath);
            $uploaded++;
        }

        $this->response->json([
            'status'   => 'ok',
            'uploaded' => $uploaded,
        ]);
    }

    public function destroy(int $id): void
    {
        $userId = (int) $this->auth->user()['id'];

        $doc = $this->documents->findById($id);
        if (!$doc || (int) $doc['user_id'] !== $userId) {
            $this->response->json(['status' => 'error', 'message' => 'Документ не найден.'], 404);
            return;
        }

        $absPath = BASE_PATH . '/public' . $doc['path'];
        if (file_exists($absPath)) {
            unlink($absPath);
        }

        $this->documents->delete($userId, $id);

        $this->response->json([
            'status' => 'ok',
            'doc_count' => $this->documents->countForUser($userId),
            'total_size' => $this->documents->totalSizeForUser($userId),
        ]);
    }

    public function download(int $id): void
    {
        $doc = $this->documents->findById($id);
        if (!$doc) {
            $this->response->json(['status' => 'error', 'message' => 'Документ не найден.'], 404);
            return;
        }

        $absPath = BASE_PATH . '/public' . $doc['path'];
        if (!file_exists($absPath)) {
            $this->response->json(['status' => 'error', 'message' => 'Файл не найден на диске.'], 404);
            return;
        }

        http_response_code(200);
        if (!headers_sent()) {
            header('Content-Type: ' . ($doc['type'] ?: 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . $doc['name'] . '"');
            header('Content-Length: ' . (string)$doc['size']);
            header('Cache-Control: no-cache');
        }
        readfile($absPath);
        exit;
    }

    public function preview(int $id): void
    {
        $doc = $this->documents->findById($id);
        if (!$doc) {
            $this->response->json(['status' => 'error', 'message' => 'Документ не найден.'], 404);
            return;
        }

        $absPath = BASE_PATH . '/public' . $doc['path'];
        if (!file_exists($absPath)) {
            $this->response->json(['status' => 'error', 'message' => 'Файл не найден на диске.'], 404);
            return;
        }

        $ext = strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION));

        $textTypes = [
            'txt', 'js', 'jsx', 'ts', 'tsx', 'php', 'css', 'html', 'htm',
            'json', 'xml', 'yaml', 'yml', 'md', 'sql', 'py', 'rb', 'java',
            'c', 'cpp', 'h', 'cs', 'go', 'rs', 'sh', 'bash', 'bat',
            'ini', 'cfg', 'conf', 'env', 'log', 'csv', 'vue', 'svelte',
        ];

        if (in_array($ext, $textTypes, true)) {
            $content = @file_get_contents($absPath);
            if ($content === false) {
                $this->response->json(['status' => 'error', 'message' => 'Не удалось прочитать файл.'], 500);
                return;
            }
            $this->response->json([
                'status'  => 'ok',
                'type'    => 'text',
                'content' => $content,
                'name'    => $doc['name'],
            ]);
            return;
        }

        if ($ext === 'zip') {
            $listing = $this->zipListing($absPath);
            if ($listing === null) {
                $this->response->json(['status' => 'error', 'message' => 'Не удалось открыть архив.'], 500);
                return;
            }
            $this->response->json([
                'status'  => 'ok',
                'type'    => 'zip',
                'name'    => $doc['name'],
                'entries' => $listing,
            ]);
            return;
        }

        $this->response->json([
            'status' => 'ok',
            'type'   => 'file',
            'url'    => $doc['path'],
            'name'   => $doc['name'],
        ]);
    }

    private function zipListing(string $path): ?array
    {
        if (!class_exists(\ZipArchive::class)) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $info = $zip->statIndex($i);
            if (!$info) continue;

            $entries[] = [
                'name'     => $info['name'],
                'size'     => $info['size'],
                'comp'     => $info['comp_size'] ?? 0,
                'is_dir'   => substr($info['name'], -1) === '/',
            ];
        }

        $zip->close();

        return $entries;
    }

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
                'error'    => (int) ($entry['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int) ($entry['size'][$i] ?? 0),
            ];
        }

        return $files;
    }
}
