<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    return HivePHP\Support\Dotenv::get($key, $default);
}