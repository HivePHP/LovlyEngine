<?php
/*
 * Copyright (c) 2025 hivephp OldVkDev
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

final class AuthController extends Controller
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

    public function register(): void
    {
        $input = $this->request->json();

        $validator = Validator::make($input, [
            'name'      => 'required|string|min:2|max:30',
            'surname'   => 'required|string|min:2|max:30',
            'email'     => 'required|email|max:100',
            'password1' => 'required|string|min:8|max:72',
            'password2' => 'required|same:password1',
            'sex'       => 'required|in:male,female',
            'country'   => 'required|string|min:2|max:50',
            'city'      => 'required|string|min:2|max:50',
            'day'       => 'required|integer',
            'month'     => 'required|integer',
            'year'      => 'required|integer|date_valid:day,month,year',
        ])->validate();

        if ($validator->fails()) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
            return;
        }

        $data  = $validator->clean();
        $email = mb_strtolower(trim($data['email']));

        if ($this->users->findByEmail($email)) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => ['email' => 'Email уже используется'],
            ], 409);
            return;
        }

        $userId = $this->users->create([
            'name'          => $data['name'],
            'surname'       => $data['surname'],
            'sex'           => $data['sex'],
            'email'         => $email,
            'password_hash' => password_hash($data['password1'], PASSWORD_DEFAULT),
            'country'       => $data['country'],
            'city'          => $data['city'],
            'day'           => $data['day'],
            'month'         => $data['month'],
            'year'          => $data['year'],
        ]);

        $this->auth->login($userId, false);

        $this->response->json([
            'status' => 'ok',
            'uid'    => $userId,
        ]);
    }

    public function login(): void
    {
        $input = $this->request->json();

        $email    = mb_strtolower(trim($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $remember = (bool)($input['remember'] ?? false);

        if ($email === '' || $password === '') {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => ['password' => 'Неверный email или пароль'],
            ], 422);
            return;
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => ['password' => 'Неверный email или пароль'],
            ], 401);
            return;
        }

        $this->auth->login((int)$user['id'], $remember);

        $this->response->json([
            'status' => 'ok',
            'uid'    => $user['id'],
        ]);
    }

    public function logout(): void
    {
        $this->auth->logout();

        $this->response->redirect('/');
    }
}