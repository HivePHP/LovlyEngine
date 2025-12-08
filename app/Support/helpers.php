<?php
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function hsc(string $text): string
{
    return htmlspecialchars($text ?? '', flags: ENT_QUOTES | ENT_SUBSTITUTE, encoding: 'UTF-8');
}