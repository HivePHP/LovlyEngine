<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace App\Controllers;

class HomeController extends BaseController
{
    public function showLogin()
    {
        echo 'Название сайта: ' . $this->config['site_name'];
    }

    public function showRegister()
    {

    }
}