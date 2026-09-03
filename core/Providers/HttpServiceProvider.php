<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
namespace HivePHP\Providers;

use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\Support\Config;
use HivePHP\Support\Container;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // Request & Response Register
        $container->set(Request::class, new Request());
        $container->set(Response::class, new Response());

        // Cookie Register
        $container->set(Cookie::class, function () {
            return new Cookie(
                Config::get('cookie')
            );
        });
    }

    public function boot(Container $container): void{}
}