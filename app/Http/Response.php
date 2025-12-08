<?php
declare(strict_types=1);

namespace HivePHP\Http;

class Response
{
    private array $headers = [];
    private int $statusCode = 200;
    private string $content = '';

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array $data, int $status = 200): void
    {
        $this->status($status)
            ->header('Content-Type', 'application/json');
        $this->send(json_encode($data));
    }

    public function send(string $content): void
    {
        $this->content = $content;
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;
        exit;
    }

    public function redirect(string $url, int $status = 302): void
    {
        $this->status($status)->header('Location', $url)->send('');
    }
}
