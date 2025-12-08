<?php
declare(strict_types=1);

namespace HivePHP\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigFactory
{
    private static ?Environment $twig = null;

    public static function create(array $config, $assetManager): void
    {
        if (self::$twig !== null) {
            return;
        }

        $views = $config['views_path'] ?? ROOT . '/views_archiv';
        $cache = $config['cache_path'] ?? ROOT . '/storage/cache/twig';

        $loader = new FilesystemLoader($views);

        self::$twig = new Environment($loader, [
            'cache'       => $cache,
            'debug'       => $config['debug'] ?? false,
            'auto_reload' => $config['auto_reload'] ?? true,
        ]);

        // CSS
        self::$twig->addFunction(new TwigFunction(
            'css',
            fn() => $assetManager->renderCss(),
            ['is_safe' => ['html']]
        ));

        // JS
        self::$twig->addFunction(new TwigFunction(
            'js',
            fn() => $assetManager->renderJs(),
            ['is_safe' => ['html']]
        ));
    }

    public static function get(): Environment
    {
        if (!self::$twig) {
            throw new \RuntimeException("Twig is not initialized. Call TwigFactory::create() first.");
        }

        return self::$twig;
    }
}
