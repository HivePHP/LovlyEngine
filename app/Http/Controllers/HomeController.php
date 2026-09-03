<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Controllers;

final class HomeController extends Controller
{
    public function showLogin(): void
    {
        $this->assets->addCss('css/home/login.css');
        $this->assets->addJs('js/home/login.js');

        $this->response->html($this->view->render('home/login', [
            'title'       => 'Авторизация',
            'users_count' => $this->users->count(),
        ]));
    }

    public function showRegister(): void
    {
        $this->assets->addCss('css/home/register.css');
        $this->assets->addJs('js/home/register.js');

        $this->response->html($this->view->render('home/register', [
            'title'       => 'Регистрация',
            'users_count' => $this->users->count(),
        ]));
    }
}
