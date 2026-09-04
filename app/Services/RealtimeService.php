<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Services;

use HivePHP\Support\Config;

/**
 * Bridges PHP and the standalone Node.js Socket.IO server (realtime/server.js).
 *
 * - Hands out short-lived HMAC-signed socket tokens so the Node server can
 *   authenticate browser sockets without any database access.
 * - Pushes realtime events to Node via a signed HTTP POST to /emit; Node then
 *   relays them to the recipient's socket room.
 *
 * Security: the shared secret lives here (from config/realtime.php / .env) and
 * in the Node server's environment. Token and push signatures are HMAC-SHA256;
 * Node verifies with constant-time comparison and a timestamp skew check.
 */
final class RealtimeService
{
    private array $config;

    public function __construct()
    {
        $this->config = Config::get('realtime');
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Signed socket handshake token for a user: "<payload>.<sig>".
     */
    public function socketToken(int $userId): string
    {
        $secret = (string) ($this->config['secret'] ?? '');
        $ttl    = (int) ($this->config['token_ttl'] ?? 300);

        $payload = self::b64url(json_encode([
            'uid' => $userId,
            'exp' => time() + $ttl,
        ], JSON_UNESCAPED_SLASHES));

        $sig = self::b64url(hash_hmac('sha256', $payload, $secret, true));

        return $payload . '.' . $sig;
    }

    /**
     * Configuration the browser needs to open a socket, or null when disabled.
     *
     * @return array<string, mixed>|null
     */
    public function clientConfig(int $userId): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        $host = (string) ($this->config['server']['host'] ?? '127.0.0.1');
        $port = (int) ($this->config['server']['port'] ?? 3001);
        $path = (string) ($this->config['server']['path'] ?? '/socket.io');
        $url  = (string) ($this->config['public_url'] ?? "http://{$host}:{$port}");

        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, -1);
        }

        return [
            'url'   => $url,
            'path'  => $path,
            'token' => $this->socketToken($userId),
        ];
    }

    /**
     * Push an event to one or more recipients in real time.
     *
     * Best-effort: failures are logged, never thrown into the request.
     */
    public function push(string $event, array $userIds, array $payload): void
    {
        if (!$this->enabled()) {
            return;
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if (!$userIds) {
            return;
        }

        $host   = (string) ($this->config['server']['host'] ?? '127.0.0.1');
        $port   = (int) ($this->config['server']['port'] ?? 3001);
        $secret = (string) ($this->config['secret'] ?? '');
        $timeout = (int) ($this->config['http_timeout'] ?? 2000);

        $ts  = time();
        $canonical = $ts . "\n" . $event . "\n" . implode(',', $userIds) . "\n"
            . json_encode($payload, JSON_UNESCAPED_SLASHES);
        $sig = self::b64url(hash_hmac('sha256', $canonical, $secret, true));

        $body = json_encode([
            'event'   => $event,
            'userIds' => $userIds,
            'payload' => $payload,
            'ts'      => $ts,
            'sig'     => $sig,
        ], JSON_UNESCAPED_SLASHES);

        $ch = curl_init("http://{$host}:{$port}/emit");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS     => $timeout,
            CURLOPT_CONNECTTIMEOUT_MS => min($timeout, 1000),
        ]);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        if ($code !== 200) {
            $this->log("push to /emit failed (code {$code}): {$response} {$error}");
        }
    }

    private function log(string $message): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        @file_put_contents(
            $dir . '/realtime.log',
            sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message),
            FILE_APPEND
        );
    }

    /**
     * Base64url (RFC 4648 §5) without padding — matches Node's base64url.
     */
    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
