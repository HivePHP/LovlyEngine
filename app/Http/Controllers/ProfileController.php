<?php
declare(strict_types=1);

namespace app\Http\Controllers;

use HivePHP\Controller;

class ProfileController  extends Controller
{
    public function editProfile(): void
    {
        $this->assets->css('main_auth.css');
        $this->assets->css('profile/editpage.css');
        echo $this->view->render('profile/editpage.twig', [

        ]);
    }

    public function updateEditProfile(): void
    {

    }

}