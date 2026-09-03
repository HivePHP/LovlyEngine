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
use App\Services\AuthService;
use HivePHP\Assets\Assets;
use HivePHP\Database\Database;
use HivePHP\Http\Cookie;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\Validation\Validator;
use HivePHP\View\View;

final class StatusController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        View $view,
        Database $db,
        Assets $assets,
        Cookie $cookies,
        UserRepository $users,
        private readonly AuthService $auth
    ) {
        parent::__construct($request, $response, $view, $db, $assets, $cookies, $users);
    }

    public function save(): void
    {
        $user = $this->auth->user();

        $input = $this->request->json();

        $validator = Validator::make($input, [
            'status' => 'nullable|string|max:120',
        ])->validate();

        if ($validator->fails()) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
            return;
        }

        $status = trim($validator->clean()['status'] ?? '');
        $status = $status === '' ? null : $status;

        $this->users->updateStatus((int)$user['id'], $status ?? '');

        $this->response->json([
            'status' => 'ok',
            'value'  => $status ?? '',
        ]);
    }
}
