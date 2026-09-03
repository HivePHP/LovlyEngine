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

final class Assets
{
    private array $css = [];
    private array $js  = [];

    private string $basePath = '/assets';

    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/');
    }

    public function addCss(string $file): void
    {
        $this->css[$file] = true;
    }

    public function addJs(string $file): void
    {
        $this->js[$file] = true;
    }

    public function renderCss(): string
    {
        $html = '';

        foreach ($this->getCssFiles() as $file) {
            $path = $this->buildPath($file);
            $html .= '<link rel="stylesheet" href="' . $path . '">' . PHP_EOL;
        }

        return $html;
    }

    public function renderJs(): string
    {
        $html = '';

        foreach ($this->getJsFiles() as $file) {
            $path = $this->buildPath($file);
            $html .= '<script type="module" src="' . $path . '" defer></script>' . PHP_EOL;
        }

        return $html;
    }

    private function getCssFiles(): array
    {
        return array_keys($this->css);
    }

    private function getJsFiles(): array
    {
        return array_keys($this->js);
    }

    private function buildPath(string $file): string
    {
        return $this->basePath . '/' . ltrim($file, '/');
    }
}