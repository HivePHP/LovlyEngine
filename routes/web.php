<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

/** @var HivePHP\Router $router */

/* GUEST ONLY */
$router->middleware('guest')->get('/', [AuthPageController::class, 'showLogin']);
$router->middleware('guest')->get('/register', [AuthPageController::class, 'showRegister']);
$router->middleware('guest')->get('/restore', [AuthPageController::class, 'showResetPassword']);
$router->middleware('guest')->post('/login', [AuthController::class, 'login']);
$router->middleware('guest')->post('/register', [AuthController::class, 'register']);


/* AUTH ONLY */
$router->middleware('auth')->get('/editprofile', [ProfileController::class, 'editProfile']);
$router->middleware('auth')->get('/logout', [AuthController::class, 'logout']);
$router->middleware('auth')->post('/user/statusUpdate', [UserController::class, 'statusUpdate']);

/* PUBLIC ROUTES */
$router->get('/id{id:\d+}', [UserController::class, 'show']);
