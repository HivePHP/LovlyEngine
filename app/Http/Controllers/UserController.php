<?php
declare(strict_types=1);

namespace app\Http\Controllers;

use HivePHP\Controller;
use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends Controller
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function show(int $id): void
    {
        $user = $this->container->get('user_model')->getUser($id);
        $auth = $this->container->get('auth'); // сервис авторизации

        $this->loadAssets();

        if (!$user)
        {
            echo $this->view->render('error_page/error_profile.twig', [
                'user_id'  => $id,
                'title' => 'Профиль не найден',
            ]);
            return;
        }

        $date = sprintf('%02d.%02d.%04d', $user['day'], $user['month'], $user['year']);

        echo $this->view->render('profile/profile.twig', [
            'user' => $user,
            'title' => $user['name'].' '.$user['surname'],

            'user_id'  => $id,
            'birth_date' => $date,
            'isOwner'    => $auth->id() === $id, // true, если владелец страницы
            'isReal'    => (bool)$user['user_real'], // true, если владелец страницы
        ]);
    }

    #[NoReturn]
    public function statusUpdate(): void
    {
        $userId = (int)$_POST['user_id'];
        $status = trim($_POST['status'] ?? '');

        // Ограничиваем длину до 200 символов
        if (strlen($status) > 186) {
            $status = mb_substr($status, 0, 186);
        }

        $userModel = $this->container->get('user_model');

        if ($status === '') {
            $userModel->statusDelete($userId);
        } else {
            $userModel->statusSet($userId, $status);
        }
        jsonResponse(['status' => $status]);
    }

    private function loadAssets(): void
    {
        $css = [
            'main_auth.css',
            'profile/profile.css',
            'profile/not_found.css',
            'profile/user_infobox.css',
            'wall/wall.css',
        ];

        $js = [
            'profile/status.js',
            'wall/wall.js',
        ];

        foreach ($css as $file) {
            $this->assets->css($file);
        }

        foreach ($js as $file) {
            $this->assets->js($file);
        }
    }
}