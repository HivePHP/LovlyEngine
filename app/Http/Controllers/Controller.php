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

use App\Repositories\UserRepository;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

abstract class Controller
{
    public function __construct(
        protected readonly Request       $request,
        protected readonly Response      $response,
        protected readonly View          $view,
        protected readonly Database      $db,
        protected readonly Assets        $assets,
        protected readonly Cookie        $cookies,
        protected readonly UserRepository $users
    ){}
}