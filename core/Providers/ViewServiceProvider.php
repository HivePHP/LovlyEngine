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
            $factory = new TwigFactory();

            return $factory->create(
                Config::get('view'),
                $c->get(Assets::class)
            );
        });

        $container->set(View::class, function (Container $c) {
            return new View(
                $c->get(Environment::class)
            );
        });
    }

    public function boot(Container $container): void
    {
        $view = $container->get(View::class);
        $auth = $container->get(AuthService::class);
        $user = $auth->user();

        $view->share('csrfToken', CsrfToken::token());

        if ($user) {
            $initials = mb_substr($user['name'], 0, 1) . mb_substr($user['surname'], 0, 1);
            $view->share('userId', $user['id']);
            $view->share('userName', $user['name'] . ' ' . $user['surname']);
            $view->share('userInitials', mb_strtoupper($initials));
        } else {
            $view->share('userId', null);
            $view->share('userName', '');
            $view->share('userInitials', '');
        }
    }
}
