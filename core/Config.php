<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP;

final class Config
{
    private static array $items = [];

    public static function load(string $name): array
    {
        if (isset(self::$items[$name])) {
            return self::$items[$name];
        }

        $path = PATH . '/configs/' . $name . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("Config [$name] not found");
        }

        return self::$items[$name] = require $path;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $item] = explode('.', $key, 2);

        $config = self::load($file);

        return $config[$item] ?? $default;
    }
}
