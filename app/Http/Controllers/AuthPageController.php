<?php
declare(strict_types=1);

namespace app\Http\Controllers;

use HivePHP\{Configs, Controller};

class AuthPageController extends Controller
{
    public function showLogin(): void
    {
        $this->assets->css('main_guest.css');
        $this->assets->css('auth/login.css');

        $count = $this->userModel->countUsers();

        echo $this->view->render('auth/login.twig', [
            'title' => Configs::get("app.site_name") . ' - Авторизация',
            'users_count'    => number_format($count, 0, ',', ' ')
        ]);
    }

    public function showRegister(): void
    {
        $this->assets->css('main_guest.css');
        $this->assets->css('auth/register.css');

        echo $this->view->render('auth/register.twig', [
            'title' => Configs::get("app.site_name") . ' - Регистрация'
        ]);
    }

    public function showResetPassword(): void
    {
        echo 'test';
    }
}
