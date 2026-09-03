<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\View;

use HivePHP\Assets\Assets;
use HivePHP\Support\Config;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Builds the Twig Environment.
 *
 * Reads all options from the config, so the only required dependency is the
 * asset manager (used for the rendered link/script helpers).
 */
final class TwigFactory
{
    public function __construct(
        private readonly Assets $assets
    ) {}

    public function create(): Environment
    {
        $twig = new Environment(
            new FilesystemLoader(BASE_PATH . '/resources/views'),
            Config::get('view')
        );

        $this->htmlFunction($twig, 'assets_css', fn () => $this->assets->renderCss());
        $this->htmlFunction($twig, 'assets_js',  fn () => $this->assets->renderJs());

        $twig->addGlobal('app', [
            'name' => Config::value('app.name', 'LovlyEngine'),
        ]);

        return $twig;
    }

    /**
     * Register a Twig function whose return value is trusted HTML.
     */
    private function htmlFunction(Environment $twig, string $name, callable $fn): void
    {
        $twig->addFunction(new TwigFunction($name, $fn, ['is_safe' => ['html']]));
    }
}
