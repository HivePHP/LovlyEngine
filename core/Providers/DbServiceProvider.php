<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
namespace HivePHP\Providers;

use HivePHP\Database\Database;
use HivePHP\Support\Config;
use HivePHP\Support\Container;

class DbServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        /* Register Connection DB */
        $db = new Database(Config::get('database'));
        $container->set(Database::class, $db);
    }
    public function boot(Container $container): void{}
}