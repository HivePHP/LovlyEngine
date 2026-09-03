<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

return [
    'path'     => env('COOKIE_PATH', '/'),
    'domain'   => env('COOKIE_DOMAIN', null),
    'secure'   => filter_var(env('COOKIE_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN),
    'httponly' => filter_var(env('COOKIE_HTTPONLY', 'true'), FILTER_VALIDATE_BOOLEAN),
    'samesite' => env('COOKIE_SAMESITE', 'Lax'),
];