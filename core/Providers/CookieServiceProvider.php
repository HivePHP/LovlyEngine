<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Configs;
use HivePHP\Security\CookieManager;

class CookieServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Загружаем конфиг cookie.php
        $config = Configs::get('cookie', []);

        // Создаём менеджер кук
        $cookie = new CookieManager($config);

        // Регистрируем в контейнере
        $this->container->set('cookie', $cookie);
    }
}