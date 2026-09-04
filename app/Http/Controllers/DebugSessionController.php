<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\View\View;

final class DebugSessionController extends Controller
{
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

    public function probe(): void
    {
        $this->response->json([
            'session_id'    => session_id(),
            'cookie_sessid' => $_COOKIE['PHPSESSID'] ?? null,
            'session_token' => $_SESSION['_csrf_token'] ?? null,
            'session_uid'   => $_SESSION['uid'] ?? null,
            'full_session'  => $_SESSION,
            'post_token'    => $_POST['csrf_token'] ?? null,
            'cookie_keys'   => array_keys($_COOKIE),
        ]);
    }
}
