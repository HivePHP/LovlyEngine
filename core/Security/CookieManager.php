<?php
declare(strict_types=1);

namespace HivePHP\Security;

class CookieManager
{
    protected array $config;

    public function __construct(array $config)
    {
        if (empty($config['key']) || strlen($config['key']) < 32) {
            throw new \RuntimeException("Cookie encryption key must be at least 32 chars.");
        }

        $this->config = $config;
    }

    // ==========================
    // УСТАНОВКА КУКИ
    // ==========================
    public function set(string $name, mixed $value, ?int $minutes = null): void
    {
        $name = $this->config['prefix'] . $name;

        // Приводим массив к JSON
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // Шифрование
        if ($this->config['encrypt']) {
            $value = $this->encrypt((string)$value);
        }

        // TTL
        $expire = time() + (($minutes ?? $this->config['lifetime'] * 1440) * 60);

        setcookie($name, $value, [
            'expires'  => $expire,
            'path'     => $this->config['path'],
            'domain'   => $this->config['domain'],
            'secure'   => $this->config['secure'],
            'httponly' => $this->config['http_only'],
            'samesite' => $this->config['same_site'],
        ]);
    }

    // ==========================
    // ЧТЕНИЕ КУКИ
    // ==========================
    public function get(string $name, mixed $default = null): mixed
    {
        $name = $this->config['prefix'] . $name;

        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = $_COOKIE[$name];

        // Дешифрование
        if ($this->config['encrypt']) {
            $value = $this->decrypt($value);
            if ($value === null) {
                return $default;
            }
        }

        // Если это JSON — вернём массив
        $json = json_decode($value, true);
        return $json !== null ? $json : $value;
    }

    // ==========================
    // УДАЛЕНИЕ КУКИ
    // ==========================
    public function delete(string $name): void
    {
        $name = $this->config['prefix'] . $name;

        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => $this->config['path'],
            'domain'   => $this->config['domain'],
            'secure'   => $this->config['secure'],
            'httponly' => $this->config['http_only'],
            'samesite' => $this->config['same_site'],
        ]);
    }

    // ==========================
    // ПРОВЕРКА
    // ==========================
    public function has(string $name): bool
    {
        return isset($_COOKIE[$this->config['prefix'] . $name]);
    }

    // ==========================
    // ШИФРОВАНИЕ
    // ==========================
    protected function encrypt(string $data): string
    {
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $this->config['key'],
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    // ==========================
    // ДЕШИФРОВАНИЕ
    // ==========================
    protected function decrypt(string $data): ?string
    {
        $raw = base64_decode($data, true);

        if ($raw === false || strlen($raw) <= 16) {
            return null;
        }

        $iv  = substr($raw, 0, 16);
        $enc = substr($raw, 16);

        return openssl_decrypt(
            $enc,
            'AES-256-CBC',
            $this->config['key'],
            OPENSSL_RAW_DATA,
            $iv
        ) ?: null;
    }
}


//$config = require ROOT . '/config/cookie.php';
//$cookie = new CookieManager($config);
//
//// Установить куку
//$cookie->set('user_id', 25);
//
//// Записать массив
//$cookie->set('settings', ['theme' => 'dark', 'lang' => 'ru']);
//
//// Прочитать
//$id = $cookie->get('user_id');
//$settings = $cookie->get('settings');
//
//// Проверить
//if ($cookie->has('user_id')) { ... }
//
//// Удалить
//$cookie->delete('user_id');

//$cookie = $this->container->get('cookie');
//
//$cookie->set('user_id', 55);
//
//$userId = $cookie->get('user_id');