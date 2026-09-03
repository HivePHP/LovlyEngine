<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Http;

final class Cookie
{
    public function __construct(
        private readonly array $config
    ) {}

    public function get(string $name, mixed $default = null): mixed
    {
        return $_COOKIE[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    public function set(string $name, string $value, int $ttl, array $options = []): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie($name, $value, [
            'expires'  => time() + $ttl,
            'path'     => $this->config['path'] ?? '/',
            'domain'   => $this->config['domain'] ?? '',
            'secure'   => $this->config['secure'] ?? false,
            'httponly' => true,
            'samesite' => $this->config['samesite'] ?? 'Lax',
        ]);
    }

    public function delete(string $name): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => $this->config['path'] ?? '/',
            'domain'   => $this->config['domain'] ?? '',
            'secure'   => $this->config['secure'] ?? false,
            'httponly' => true,
            'samesite' => $this->config['samesite'] ?? 'Lax',
        ]);
    }
}