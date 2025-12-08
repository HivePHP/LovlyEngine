<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Router;
use HivePHP\Template\LayoutManager;

class RouteServiceProvider extends ServiceProvider
{
    protected Router $router;

    public function register(): void
    {
        $this->router = new Router($this->container);
        $this->container->set('router', $this->router);
    }

    public function boot(): void
    {
        $router = $this->container->get('router');

        (function($router) {
            require ROOT . '/routes/web.php';
        })($router);

        LayoutManager::handle($this->container);
        $router->dispatch();
    }
}
