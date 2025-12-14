<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */


namespace HivePHP;

class AssetsManager
{
    private static array $css = [];
    private static array $js  = [];

    /* ---------- CSS ---------- */

    public static function addCss(string $path): void
    {
        self::$css[$path] = $path;
    }

    public static function css(): string
    {
        $html = '';
        foreach (self::$css as $file) {
            $html .= '<link rel="stylesheet" href="' . $file . '">' . PHP_EOL;
        }
        return $html;
    }

    /* ---------- JS ---------- */

    public static function addJs(string $path): void
    {
        self::$js[$path] = $path;
    }

    public static function js(): string
    {
        $html = '';
        foreach (self::$js as $file) {
            $html .= '<script src="' . $file . '"></script>' . PHP_EOL;
        }
        return $html;
    }

    /* ---------- RESET ---------- */

    public static function reset(): void
    {
        self::$css = [];
        self::$js  = [];
    }
}