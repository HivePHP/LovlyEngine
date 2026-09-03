<?php

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

final class EditProfileController extends Controller
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

    public function show(): void
    {
        $user = $this->auth->user();

        $this->assets->addCss('css/profile/profile.css');
        $this->assets->addCss('css/home/edit-profile.css');

        $this->response->html($this->view->render('profile/edit', [
            'title'           => 'Редактирование профиля',
            'user_id'         => $user['id'],
            'name'            => $user['name'],
            'surname'         => $user['surname'],
            'about'           => $user['about'] ?? '',
            'interests'       => $user['interests'] ?? '',
            'favorite_films'  => $user['favorite_films'] ?? '',
        ]));
    }

    public function save(): void
    {
        $user = $this->auth->user();

        $input = $this->request->json();

        $validator = Validator::make($input, [
            'about'          => 'nullable|string|max:1000',
            'interests'      => 'nullable|string|max:1000',
            'favorite_films' => 'nullable|string|max:1000',
        ])->validate();

        if ($validator->fails()) {
            $this->response->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
            return;
        }

        $data = $validator->clean();

        $this->users->updateProfile(
            (int)$user['id'],
            $data['about'] ?? '',
            $data['interests'] ?? '',
            $data['favorite_films'] ?? ''
        );

        $this->response->json(['status' => 'ok']);
    }
}
