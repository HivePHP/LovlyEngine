<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use App\Services\AuthService;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $db     = $this->container->get('db');
        $cookie = $this->container->get('cookie');
        $userModel = $this->container->get('user_model');

        $this->container->set('auth', new AuthService(
            $db,
            $userModel,
            $cookie
        ));
    }
}
