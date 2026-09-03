<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use HivePHP\Assets\Assets;
use HivePHP\Support\Config;

final class TwigFactory
{
    public function create(array $config, Assets $assets): Environment
    {
        $assets->setBasePath('/assets');

        $loader = new FilesystemLoader(BASE_PATH . '/resources/views');
        $twig   = new Environment($loader, $config);

        $app = Config::get('app');

        $twig->addGlobal('app', [
            'name' => $app['name'],
        ]);

        // assets helpers
        $twig->addFunction(new TwigFunction(
            'assets_css',
            fn () => $assets->renderCss(),
            ['is_safe' => ['html']]
        ));

        $twig->addFunction(new TwigFunction(
            'assets_js',
            fn () => $assets->renderJs(),
            ['is_safe' => ['html']]
        ));

        return $twig;
    }
}