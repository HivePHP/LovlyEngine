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

use App\Http\Kernel;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\Http\Router;
use HivePHP\Support\Container;

class RouterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $kernel = new Kernel();
        $container->set(Kernel::class, $kernel);

        $router = new Router($container, $kernel);
        $container->set(Router::class, $router);

        require BASE_PATH . '/routes/web.php';
    }

    public function boot(Container $container): void
    {
        $router  = $container->get(Router::class);
        $request = $container->get(Request::class);
        $response = $container->get(Response::class);

        $router->dispatch($request, $response);
    }
}
