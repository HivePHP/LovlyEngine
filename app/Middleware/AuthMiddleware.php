<?php
declare(strict_types=1);

namespace App\Middleware;

use HivePHP\Container;

class AuthMiddleware
{
    private string $mode;

    public function __construct(Container $container, string $mode = 'auth')
    {
        $this->container = $container;
        $this->mode = $mode; // auth | guest
    }

    public function handle(): bool
    {
        $auth = $this->container->get('auth');

        // режим: только авторизованные
        if ($this->mode === 'auth') {
            if (!$auth->check()) {
                header("Location: /");
                return false;
            }
        }

        // режим: только гости
        if ($this->mode === 'guest') {
            if ($auth->check()) {
                $user = $auth->user();
                header("Location: /id{$user['id']}");
                return false;
            }
        }

        return true;
    }
}
