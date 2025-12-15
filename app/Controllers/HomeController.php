<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Controllers;

use HivePHP\AssetsManager;
use HivePHP\View;

class HomeController extends BaseController
{
    public function showLogin(): void
    {
//        echo 'Название сайта: ' . $this->config['site_name'];

        AssetsManager::addCss('/css/home/login.css');

        View::render('home/login',
            ['title' => 'Привет!']
        );
    }

    public function showRegister(): void
    {
        View::render('home/register',
            ['title' => 'Привет!']
        );
    }
}