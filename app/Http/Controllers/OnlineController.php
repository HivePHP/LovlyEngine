<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\OnlineService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

final class OnlineController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly AuthService $auth,
        private readonly OnlineService $online
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    public function heartbeat(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->json(['status' => 'error'], 401);
            return;
        }

        $this->online->heartbeat((int)$user['id']);

        $this->response->json(['status' => 'ok']);
    }

    /**
     * Mark user as offline immediately on page close.
     * Called via sendBeacon (GET, no CSRF needed).
     */
    public function leave(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->json(['status' => 'ok']);
            return;
        }

        $this->online->markOffline((int)$user['id']);

        $this->response->json(['status' => 'ok']);
    }

    public function status(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->json(['status' => 'error'], 401);
            return;
        }

        $input = $this->request->json();
        $userId = (int) ($input['user_id'] ?? $user['id']);
        $isOnline = $this->online->isOnline($userId);

        $this->response->json([
            'status'  => 'ok',
            'online'  => $isOnline,
            'user_id' => $userId,
        ]);
    }

    public function batch(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->json(['status' => 'error'], 401);
            return;
        }

        $input = $this->request->json();
        $ids = $input['user_ids'] ?? [];

        if (!is_array($ids) || count($ids) > 100) {
            $this->response->json(['status' => 'error', 'message' => 'Invalid user_ids'], 422);
            return;
        }

        $statuses = $this->online->getOnlineStatuses(array_map('intval', $ids));

        $this->response->json([
            'status' => 'ok',
            'online' => $statuses,
        ]);
    }
}
