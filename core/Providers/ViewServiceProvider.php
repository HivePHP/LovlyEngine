<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Template\TwigFactory;
use HivePHP\Configs;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        TwigFactory::create(
            Configs::get('twig', []),
            $this->container->get('assets')
        );

        $twig = TwigFactory::get();

        $this->globAssets($twig);

        $this->container->set('view', $twig);
    }

    private function globAssets($twig): void
    {
        $auth = $this->container->get('auth');

        /* Глобальный вывод в шаблоны*/
        $twig->addGlobal('site', [
            'name' => Configs::get('app.site_name'),
            'year' => date('Y'),
            'my_page' => $auth->id(), // Потом доработать
        ]);

        $twig->addGlobal('assets', [
            'css' => '/css/',
            'js' => '/js/',
            'img' => '/img/',
        ]);

        /* Подключаем глобальные стили */
        $this->assets()->js('auth.js');

        $this->assets()->js('component/dropMenu.js');
        $this->assets()->css('component/dropMenu.css');

        $this->assets()->css('component/header.css');
        $this->assets()->css('component/sidebar.css');
        $this->assets()->css('component/footer_auth.css');

        /* Errors */
        $this->assets()->css('component/input_error.css');
        $this->assets()->css('component/general_error.css');

        /* Modal Box */
        $this->assets()->js('component/modalBox.js');
        $this->assets()->css('component/modal_box.css');

        /* Button */
        $this->assets()->css('component/button.css');

        /* Input */
        $this->assets()->css('component/input.css');

    }
}
