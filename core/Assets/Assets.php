<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Assets;

use RuntimeException;

/**
 * Dependency-based static asset manager (OldVK-style).
 *
 * Pages declare what they need via usePage(); the renderers emit a single
 * content-hashed URL per asset, so browser/edge caches can be long-lived
 * (see public/.htaccess) without stale-content risk.
 */
final class Assets
{
    /** @var array<string, true> css bundles in registration order */
    private array $css = [];

    /** @var array<string, true> js entries in registration order */
    private array $js = [];

    private array $config;
    private array $manifest = [];
    private bool $manifestLoaded = false;

    public function __construct(array $config = [])
    {
        $this->config = $config + [
            'base_url'    => '/assets',
            'manifest'    => 'storage/cache/assets/manifest.php',
            'css_bundles' => [],
            'pages'       => [],
        ];
    }

    /* ===================== */
    /* Registration          */
    /* ===================== */

    /**
     * Register everything a page needs (from the assets config).
     */
    public function usePage(string $page): void
    {
        $pages = $this->config['pages'] ?? [];

        if (!isset($pages[$page])) {
            throw new RuntimeException("Unknown asset page [{$page}]");
        }

        foreach ($pages[$page]['css'] ?? [] as $bundle) {
            $this->addCss($bundle);
        }

        foreach ($pages[$page]['js'] ?? [] as $entry) {
            $this->addJs($entry);
        }
    }

    /**
     * Register a CSS bundle (e.g. 'app', 'profile-page').
     */
    public function addCss(string $bundle): void
    {
        $this->css[$bundle] = true;
    }

    /**
     * Register a JS entry module (e.g. 'js/pages/shell.js').
     */
    public function addJs(string $entry): void
    {
        $this->js[$entry] = true;
    }

    /* ===================== */
    /* Rendering             */
    /* ===================== */

    public function renderCss(): string
    {
        $html = '';

        foreach (array_keys($this->css) as $bundle) {
            $html .= '<link rel="stylesheet" href="' . $this->cssUrl($bundle) . '">' . PHP_EOL;
        }

        return $html;
    }

    public function renderJs(): string
    {
        $html = '';

        foreach (array_keys($this->js) as $entry) {
            $html .= '<script type="module" src="' . $this->jsUrl($entry) . '"></script>' . PHP_EOL;
        }

        return $html;
    }

    /* ===================== */
    /* URL resolution        */
    /* ===================== */

    private function cssUrl(string $bundle): string
    {
        $map = $this->manifestMap('css');

        if (isset($map[$bundle])) {
            return $this->baseUrl() . '/' . ltrim($map[$bundle], '/');
        }

        // Dev fallback: source bundle path (page.css.name).
        $name = str_replace(['/', '\\'], '-', $bundle) . '.css';
        return $this->baseUrl() . '/css/' . $name;
    }

    private function jsUrl(string $entry): string
    {
        $map = $this->manifestMap('js');

        if (isset($map[$entry])) {
            return $this->baseUrl() . '/' . ltrim($map[$entry], '/');
        }

        // Dev fallback: source module path.
        return $this->baseUrl() . '/' . ltrim($entry, '/');
    }

    private function baseUrl(): string
    {
        return rtrim($this->config['base_url'], '/');
    }

    private function manifestMap(string $type): array
    {
        $manifest = $this->loadManifest();
        return $manifest[$type] ?? [];
    }

    private function loadManifest(): array
    {
        if ($this->manifestLoaded) {
            return $this->manifest;
        }

        $this->manifestLoaded = true;

        $path = BASE_PATH . '/' . ltrim($this->config['manifest'], '/');

        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                $this->manifest = $data;
            }
        }

        return $this->manifest;
    }
}
