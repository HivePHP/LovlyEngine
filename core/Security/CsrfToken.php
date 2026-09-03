<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Security;

final class CsrfToken
{
    private const SESSION_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generate(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return self::generate();
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(string $token): bool
    {
        if (empty($_SESSION[self::SESSION_KEY]) || $token === '') {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    public static function refresh(): string
    {
        return self::generate();
    }
}
