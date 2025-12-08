<?php
declare(strict_types=1);

namespace HivePHP\Http;

class Request
{
    private array $get = [];
    private array $post = [];
    private array $server = [];
    private array $headers = [];
    private array $json = [];

    public function __construct()
    {
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->server = $_SERVER;
        $this->headers = getallheaders() ?: [];

        // Попробуем декодировать JSON тело
        $input = file_get_contents('php://input');
        if ($input) {
            $json = json_decode($input, true);
            $this->json = is_array($json) ? $json : [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->json[$key] ?? $default;
    }

    public function json(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json);
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function header(string $name, mixed $default = null): mixed
    {
        return $this->headers[$name] ?? $default;
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }
}
