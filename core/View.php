<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\TwigFunction;

final class View
{
    private static ?Environment $twig = null;

    public static function init(array $config): void
    {
        if (self::$twig !== null) {
            return;
        }

        $loader = new FilesystemLoader($config['path']);

        self::$twig = new Environment($loader, [
            'cache' => $config['cache'],
            'debug' => $config['debug'],
            'auto_reload' => true,
        ]);

        self::$twig->addFunction(new TwigFunction('css', fn () => AssetsManager::css(), ['is_safe' => ['html']]));
        self::$twig->addFunction(new TwigFunction('js', fn () => AssetsManager::js(), ['is_safe' => ['html']]));

        if ($config['debug']) {
            self::$twig->addExtension(new DebugExtension());
        }
    }

    public static function render(string $template, array $data = []): void
    {
        echo self::$twig->render($template . '.twig', $data);
//        ..AssetsManager::reset();
    }

    public static function twig(): Environment
    {
        return self::$twig;
    }
}