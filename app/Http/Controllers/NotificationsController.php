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

use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\Support\Config;
use HivePHP\View\View;

final class NotificationsController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly NotificationRepository $notifications,
        private readonly AuthService $auth,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    /**
     * GET /notifications — full notifications page.
     */
    public function page(): void
    {
        $userId = (int)$this->auth->user()['id'];
        $maxItems = (int) Config::value('realtime.notification.max_items', 30);

        $this->assets->usePage('notifications');

        $this->response->html($this->view->render('notifications/notifications', [
            'title'      => 'Уведомления',
            'viewer_id'  => $userId,
            'notifications' => $this->notifications->recent($userId, $maxItems),
            'unread'     => $this->notifications->countUnread($userId),
        ]));
    }

    /**
     * GET /api/notifications — list + unread totals for the shell.
     */
    public function index(): void
    {
        $userId = (int)$this->auth->user()['id'];

        $maxItems = (int) Config::value('realtime.notification.max_items', 30);

        $this->response->json([
            'status'          => 'ok',
            'items'           => $this->notifications->recent($userId, $maxItems),
            'unread'          => $this->notifications->countUnread($userId),
            'unread_by_section' => $this->notifications->countUnreadBySection($userId),
        ]);
    }

    /**
     * POST /api/notifications/{id}/read — mark a single notification as read.
     */
    public function read(int $id): void
    {
        $userId = (int)$this->auth->user()['id'];

        $this->notifications->markRead($userId, (int)$id);

        $this->response->json([
            'status' => 'ok',
            'unread' => $this->notifications->countUnread($userId),
        ]);
    }

    /**
     * POST /api/notifications/read-all — mark everything as read.
     */
    public function readAll(): void
    {
        $userId = (int)$this->auth->user()['id'];

        $this->notifications->markAllRead($userId);

        $this->response->json([
            'status' => 'ok',
            'unread' => 0,
            'unread_by_section' => [],
        ]);
    }

    /**
     * POST /api/notifications/{id}/delete — delete a single notification.
     */
    public function destroy(int $id): void
    {
        $userId = (int)$this->auth->user()['id'];

        $this->notifications->delete($userId, (int)$id);

        $this->response->json([
            'status' => 'ok',
            'unread' => $this->notifications->countUnread($userId),
            'unread_by_section' => $this->notifications->countUnreadBySection($userId),
        ]);
    }

    /**
     * POST /api/notifications/delete-all — delete all notifications.
     */
    public function destroyAll(): void
    {
        $userId = (int)$this->auth->user()['id'];

        $this->notifications->deleteAll($userId);

        $this->response->json([
            'status' => 'ok',
            'unread' => 0,
            'unread_by_section' => [],
        ]);
    }
}
