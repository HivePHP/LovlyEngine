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
use JsonException;

final class UserController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
    ];

    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    /**
     * @throws JsonException
     */
    public function show(int $id): void
    {
        $user = $this->users->findProfileById($id);

        if (!$user) {
            $this->response->json(['status' => 'error', 'message' => 'User not found'], 404);
            return;
        }

        $month = (int)$user['month'];
        $day   = (int)$user['day'];
        $year  = (int)$user['year'];
        $birthday = $day . ' ' . (self::MONTH_NAMES[$month] ?? '') . ' ' . $year . ' г.';

        $this->assets->usePage('profile');

        $this->response->html($this->view->render('profile/profile', [
            'title'           => trim($user['name'] . ' ' . $user['surname']),
            'name'            => $user['name'],
            'surname'         => $user['surname'],
            'status'          => $user['status'] ?? '',
            'sex'             => $user['sex'],
            'city'            => $user['city'],
            'country'         => $user['country'],
            'birthday'        => $birthday,
            'user_id'         => $user['id'],
            'about'           => $user['about'] ?? '',
            'interests'       => $user['interests'] ?? '',
            'favorite_films'  => $user['favorite_films'] ?? '',
        ]));
    }
}
