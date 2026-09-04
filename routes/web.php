<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

use App\Http\Controllers\AlbumsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvatarUploadController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\DebugSessionController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OnlineController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use HivePHP\Http\Router;

/** @var Router $router */

$router->middleware('guest', 'web')->       get('/', [HomeController::class, 'showLogin']);
$router->middleware('guest', 'web')->       get('/reg', [HomeController::class, 'showRegister']);
$router->                                  get('/captcha/image', [CaptchaController::class, 'image']);
$router->middleware('guest')->              post('/login', [AuthController::class, 'login']);
$router->middleware('guest')->              post('/register', [AuthController::class, 'register']);
$router->middleware('auth')->                post('/logout', [AuthController::class, 'logout']);

$router->middleware('web')->get('/id{id}', [UserController::class, 'show']);

$router->middleware('web')->get('/albums/id{id}', [AlbumsController::class, 'index']);
$router->middleware('web')->get('/album/{id}', [AlbumsController::class, 'show']);

$router->middleware('auth')->post('/api/albums/create', [AlbumsController::class, 'create']);
$router->middleware('auth')->post('/api/albums/reorder', [AlbumsController::class, 'reorder']);
$router->middleware('auth')->post('/api/albums/{id}/delete', [AlbumsController::class, 'destroy']);
$router->middleware('auth')->post('/api/albums/{id}/photos', [AlbumsController::class, 'upload']);
$router->middleware('auth')->post('/api/albums/{id}/photos/reorder', [AlbumsController::class, 'reorderPhotos']);
$router->middleware('auth')->post('/api/photos/{id}/delete', [AlbumsController::class, 'deletePhoto']);

$router->middleware('auth', 'web')->get('/editprofile', [EditProfileController::class, 'show']);
$router->middleware('auth', 'web')->get('/friends', [FriendsController::class, 'index']);
$router->middleware('auth')->post('/api/profile/update', [EditProfileController::class, 'save']);
$router->middleware('auth')->post('/api/profile/status', [StatusController::class, 'save']);
$router->middleware('auth')->post('/api/profile/avatar', [AvatarUploadController::class, 'save']);
$router->middleware('auth')->post('/api/profile/avatar/delete', [AvatarUploadController::class, 'delete']);

$router->middleware('auth')->post('/api/online/heartbeat', [OnlineController::class, 'heartbeat']);
$router->middleware('auth')->get('/api/online/leave', [OnlineController::class, 'leave']);
$router->middleware('auth')->post('/api/online/status', [OnlineController::class, 'status']);
$router->middleware('auth')->post('/api/online/batch', [OnlineController::class, 'batch']);

$router->middleware('auth')->post('/api/friends/{id}/add', [FriendsController::class, 'add']);
$router->middleware('auth')->post('/api/friends/{id}/accept', [FriendsController::class, 'accept']);
$router->middleware('auth')->post('/api/friends/{id}/decline', [FriendsController::class, 'decline']);
$router->middleware('auth')->post('/api/friends/{id}/remove', [FriendsController::class, 'remove']);

$router->middleware('auth')->get('/api/notifications', [NotificationsController::class, 'index']);
$router->middleware('auth')->post('/api/notifications/read-all', [NotificationsController::class, 'readAll']);
$router->middleware('auth')->post('/api/notifications/{id}/read', [NotificationsController::class, 'read']);
$router->middleware('auth')->post('/api/notifications/{id}/delete', [NotificationsController::class, 'destroy']);
$router->middleware('auth')->post('/api/notifications/delete-all', [NotificationsController::class, 'destroyAll']);
$router->middleware('auth', 'web')->get('/notifications', [NotificationsController::class, 'page']);

$router->middleware('auth', 'web')->get('/documents', [DocumentsController::class, 'page']);
$router->middleware('auth')->post('/api/documents/upload', [DocumentsController::class, 'upload']);
$router->middleware('auth')->post('/api/documents/{id}/delete', [DocumentsController::class, 'destroy']);
$router->middleware('web')->get('/api/documents/{id}/preview', [DocumentsController::class, 'preview']);
$router->middleware('web')->get('/api/documents/{id}/download', [DocumentsController::class, 'download']);
$router->middleware('web')->get('/user/{id}/documents', [DocumentsController::class, 'userPage']);

$router->middleware('auth', 'web')->get('/messages', [MessagesController::class, 'index']);
$router->middleware('auth')->get('/api/messages/conversations', [MessagesController::class, 'conversations']);
$router->middleware('auth')->get('/api/messages/unread', [MessagesController::class, 'unread']);
$router->middleware('auth')->post('/api/messages/{otherId}/send', [MessagesController::class, 'send']);
$router->middleware('auth')->get('/api/messages/{otherId}', [MessagesController::class, 'thread']);

$router->middleware('auth')->post('/api/messages/delete-batch', [MessagesController::class, 'deleteBatch']);
$router->middleware('auth')->post('/api/messages/{otherId}/delete-conversation', [MessagesController::class, 'deleteConversation']);
$router->middleware('auth')->post('/api/messages/{messageId}/delete', [MessagesController::class, 'deleteMessage']);

$router->get('/debug/session', [DebugSessionController::class, 'probe']);