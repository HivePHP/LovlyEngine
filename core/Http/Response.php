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

final class Response
{
    public function html(string $content, int $status = 200): void
    {
        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        echo $content;
    }

    public function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    public function redirect(string $url, int $status = 302): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }
}
