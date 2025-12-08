<?php
declare(strict_types=1);

namespace HivePHP\Security;

class CsrfManager
{
    public function __construct(protected TokenGenerator $tokens) {}

    public function generate(): string
    {
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = $this->tokens->base64Url(32);
        }

        return $_SESSION['csrf'];
    }

    public function check(?string $token): bool
    {
        return $token !== null &&
            isset($_SESSION['csrf']) &&
            hash_equals($_SESSION['csrf'], $token);
    }
}