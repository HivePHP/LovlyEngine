<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use HivePHP\Http\Router;

/** @var Router $router */

$router->middleware('guest', 'web')->       get('/', [HomeController::class, 'showLogin']);
$router->middleware('guest', 'web')->       get('/reg', [HomeController::class, 'showRegister']);
$router->middleware('guest')->              post('/login', [AuthController::class, 'login']);
$router->middleware('guest')->              post('/register', [AuthController::class, 'register']);
$router->middleware('auth')->                post('/logout', [AuthController::class, 'logout']);

$router->middleware('web')->get('/id{id}', [UserController::class, 'show']);

$router->middleware('auth', 'web')->get('/editprofile', [EditProfileController::class, 'show']);
$router->middleware('auth')->post('/api/profile/update', [EditProfileController::class, 'save']);