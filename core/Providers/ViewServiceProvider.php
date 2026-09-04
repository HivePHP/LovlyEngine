<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Providers;

use App\Repositories\NotificationRepository;
use App\Services\AuthService;
use App\Services\RealtimeService;
use HivePHP\Assets\Assets;
use HivePHP\Security\CsrfToken;
use HivePHP\Support\Config;
use HivePHP\Support\Container;
use HivePHP\View\TwigFactory;
use HivePHP\View\View;
use Twig\Environment;

class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(Environment::class, function (Container $c) {
            return (new TwigFactory($c->get(Assets::class)))->create();
        });

        $container->set(View::class, function (Container $c) {
            return new View($c->get(Environment::class));
        });
    }

    public function boot(Container $container): void
    {
        $view = $container->get(View::class);

        $view->share('csrfToken', CsrfToken::token());

        $config = Config::get('realtime');
        $assets = Config::get('assets');
        $view->share(
            'realtime_client_js',
            ($assets['base_url'] ?? '/assets') . '/' . ltrim($config['client_source'] ?? 'socketio/socket.io.js', '/')
        );

        $user = $container->get(AuthService::class)->user();

        if (!$user) {
            $view->shareMany([
                'userId'       => null,
                'userName'     => '',
                'userInitials' => '',
                'userAvatar'   => '',
                'realtime'     => null,
            ]);
            return;
        }

        $initials = mb_strtoupper(
            mb_substr($user['name'], 0, 1) . mb_substr($user['surname'], 0, 1)
        );

        $this->shareNotificationState($view, $container, (int) $user['id']);

        $view->shareMany([
            'userId'       => $user['id'],
            'userName'     => trim($user['name'] . ' ' . $user['surname']),
            'userInitials' => $initials,
            'userAvatar'   => $user['avatar'] ?? '',
            'realtime'     => $container->get(RealtimeService::class)->clientConfig((int) $user['id']),
        ]);
    }

    /**
     * Share pre-rendered bell/badge data (recent notifications + unread counts).
     */
    private function shareNotificationState(View $view, Container $container, int $userId): void
    {
        $repo = $container->get(NotificationRepository::class);

        $maxItems = (int) Config::value('realtime.notification.max_items', 30);

        $view->shareMany([
            'notifications'    => $repo->recent($userId, $maxItems),
            'unread_total'     => $repo->countUnread($userId),
            'unread_by_section'=> $repo->countUnreadBySection($userId),
        ]);
    }
}
