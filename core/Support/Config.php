<?php

declare(strict_types=1);

namespace HivePHP\Support;

use RuntimeException;

final class Config
{
    /** @var array<string, array> */
    private static array $loaded = [];

    public static function get(string $name): array
    {
        if (!isset(self::$loaded[$name])) {
            $path = BASE_PATH . "/config/{$name}.php";

            if (!is_file($path)) {
                throw new RuntimeException("Config file [{$name}] not found");
            }

            $config = require $path;

            if (!is_array($config)) {
                throw new RuntimeException("Config [{$name}] must return array");
            }

            self::$loaded[$name] = $config;
        }

        return self::$loaded[$name];
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        $config = self::get($file);

        if ($path === null) {
            return $config;
        }

        foreach (explode('.', $path) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }
}