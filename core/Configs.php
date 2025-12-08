<?php
declare(strict_types=1);

namespace HivePHP;

class Configs
{
    private static array $configs = [];

    public static function load(string $path): void
    {
        foreach (glob($path . '/*.php') ?: [] as $file) {
            $name = basename($file, '.php');
            self::$configs[$name] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $value = self::$configs;

        foreach ($parts as $part) {
            if (!array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    public static function all(): array
    {
        return self::$configs;
    }
}
