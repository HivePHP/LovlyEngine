<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Support;

final class Dotenv
{
    public static function load(string $path): void
    {
        $file = $path . '/.env';

        if (!is_file($file)) {
            $file = $path . '/.env.example';
        }

        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (strlen($value) >= 2 && ($value[0] === '"' && $value[-1] === '"' || $value[0] === "'" && $value[-1] === "'")) {
                    $value = substr($value, 1, -1);
                    $value = str_replace(['\\"', "\\'"], ['"', "'"], $value);
                }

                if (!getenv($key) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}