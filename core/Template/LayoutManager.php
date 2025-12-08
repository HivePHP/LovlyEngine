<?php
declare(strict_types=1);

namespace HivePHP\Template;

use HivePHP\Container;

class LayoutManager
{
    public static function handle(Container $container): void
    {
        $twig = $container->get('view');
        $isAuth = $container->get('auth');

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

        // Передаём в Twig переменную
        $twig->addGlobal('isAuth', $isAuth->check());

        // Гость, но на авторизационных страницах — old VK guest layout
        $guestPages = ['/', '/login', '/register'];
        if (!$isAuth->check() && in_array($uri, $guestPages, true)) {
            $twig->addGlobal('layout', 'layouts/guest.twig');
            return;
        }

        // Гость в других местах — показываем layout авторизованных
        if ($isAuth->check()) {
            $twig->addGlobal('layout', 'layouts/auth.twig');
            return;
        }

        // Авторизованный пользователь — всегда auth layout
        $twig->addGlobal('layout', 'layouts/auth.twig');
    }
}