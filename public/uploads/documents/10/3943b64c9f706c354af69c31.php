<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

final class ProfileController
{
    public function __construct(
        private readonly View $view,
        private readonly UserRepository $users,
        private readonly Session $session
    ) {}

    public function showById(Request $request, string $id): Response
    {
        $profileUser = $this->users->findById($id);
        
        if (!$profileUser) {
            // Fallback for non-existing users (demo)
            $profileUser = new User(
                id: (int)$id,
                first_name: 'Павел',
                last_name: 'Дуров',
                email: 'durov@example.com',
                password: '',
                city: 'Санкт-Петербург',
                country: 'Россия',
                birth_date: '1984-10-10',
                username: 'durov',
                is_online: 1,
            );
        }

        $currentUser = $this->session->user();
        $isOwnProfile = $currentUser && $currentUser->id === $profileUser->id;

        $layout = $currentUser ? 'layouts.auth' : 'layouts.guest';

        $html = $this->view->render('profile.show', [
            'title' => $profileUser->getFullName(),
            'user' => $profileUser,
            'currentUser' => $currentUser,
            'isOwnProfile' => $isOwnProfile,
            'layout' => $layout,
        ], $layout);

        return new Response($html);
    }

    public function showByUsername(Request $request, string $username): Response
    {
        $profileUser = $this->users->findByUsername($username);
        
        if (!$profileUser) {
            $profileUser = new User(
                id: 1,
                first_name: 'Пользователь',
                last_name: $username,
                email: "$username@example.com",
                password: '',
                city: '',
                country: '',
                birth_date: '1990-01-01',
                username: $username,
                is_online: 0,
            );
        }

        $currentUser = $this->session->user();
        $isOwnProfile = $currentUser && $currentUser->id === $profileUser->id;

        $layout = $currentUser ? 'layouts.auth' : 'layouts.guest';

        $html = $this->view->render('profile.show', [
            'title' => $profileUser->getFullName(),
            'user' => $profileUser,
            'currentUser' => $currentUser,
            'isOwnProfile' => $isOwnProfile,
            'layout' => $layout,
        ], $layout);

        return new Response($html);
    }
}