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

use App\Services\AuthService;
use HivePHP\Assets\Assets;
use HivePHP\Security\CsrfToken;
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

        $user = $container->get(AuthService::class)->user();

        if (!$user) {
            $view->shareMany([
                'userId'       => null,
                'userName'     => '',
                'userInitials' => '',
                'userAvatar'   => '',
            ]);
            return;
        }

        $initials = mb_strtoupper(
            mb_substr($user['name'], 0, 1) . mb_substr($user['surname'], 0, 1)
        );

        $view->shareMany([
            'userId'       => $user['id'],
            'userName'     => trim($user['name'] . ' ' . $user['surname']),
            'userInitials' => $initials,
            'userAvatar'   => $user['avatar'] ?? '',
        ]);
    }
}
