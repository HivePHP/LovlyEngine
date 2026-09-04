<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Http;

final class Request
{
    private ?string $rawBody = null;

    public function json(int $maxSize = 1048576): array
    {
        if ($this->getMethod() !== 'POST') {
            throw new HttpException(405,'Method not allowed');
        }

        $this->assertJsonContentType();

        $raw = $this->getRawBody();

        if ($raw === '') {
            throw new HttpException(405,'Empty request body');
        }

        if (strlen($raw) > $maxSize) {
            throw new HttpException(413,'Request body too long');
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new HttpException(400,'Invalid JSON');
        }

        return $data;
    }

    private function assertJsonContentType(): void
    {
        $type = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($type, 'application/json') === false) {
            throw new HttpException(415,'Invalid Content-Type');
        }
    }

    private function getRawBody(): string
    {
        if ($this->rawBody !== null) {
            return $this->rawBody;
        }

        $body = file_get_contents('php://input');

        if ($body === false) {
            throw new HttpException(400,'Failed to read request body');
        }

        return $this->rawBody = $body;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    /**
     * Return one uploaded file entry from $_FILES, or null if absent.
     *
     * @return array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int}|null
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key])
            && ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }


    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}