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

final class RateLimiter
{
    private const WINDOW = 60;
    private const MAX_REQUESTS = 60;

    public static function check(string $key, int $maxRequests = self::MAX_REQUESTS, int $window = self::WINDOW): bool
    {
        $now = time();
        $sessionKey = '_rate_' . $key;

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }

        $_SESSION[$sessionKey] = array_filter(
            $_SESSION[$sessionKey],
            static fn(int $ts) => $ts > $now - $window
        );

        if (count($_SESSION[$sessionKey]) >= $maxRequests) {
            return false;
        }

        $_SESSION[$sessionKey][] = $now;
        return true;
    }

    public static function remaining(string $key, int $maxRequests = self::MAX_REQUESTS, int $window = self::WINDOW): int
    {
        $now = time();
        $sessionKey = '_rate_' . $key;

        if (!isset($_SESSION[$sessionKey])) {
            return $maxRequests;
        }

        $recent = array_filter(
            $_SESSION[$sessionKey],
            static fn(int $ts) => $ts > $now - $window
        );

        return max(0, $maxRequests - count($recent));
    }
}
