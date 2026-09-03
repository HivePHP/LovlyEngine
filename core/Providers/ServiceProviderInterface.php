<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Support\Container;

interface ServiceProviderInterface
{
    public function register(Container $container): void;
    public function boot(Container $container): void;
}