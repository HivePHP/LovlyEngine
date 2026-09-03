<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use HivePHP\Assets\Assets;
use HivePHP\Http\MiddlewareInterface;
use HivePHP\Http\Request;

final class WebAssets implements MiddlewareInterface
{
    public function __construct(
        private readonly Assets $assets
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/components/button.css');
        $this->assets->addCss('css/profile/profile.css');
        $this->assets->addCss('css/home/login.css');
        $this->assets->addCss('css/home/register.css');
        $this->assets->addCss('css/home/edit-profile.css');

        $this->assets->addJs('js/app/shell.js');

        $next($request);
    }
}
