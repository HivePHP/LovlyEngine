<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
namespace HivePHP\Providers;

use HivePHP\Assets\Assets;
use HivePHP\Support\Config;
use HivePHP\Support\Container;

class AssetsServiceProvider implements ServiceProviderInterface
{
     public function register(Container $container): void
     {
         $container->set(Assets::class, new Assets(Config::get('assets')));
     }

     public function boot(Container $container): void{}
}