<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */


namespace App\Controllers;

use HivePHP\AssetsManager;

class AssetsController extends BaseController
{
    public function loadAssets(): void
    {
        AssetsManager::addCss('/assets/css/layout.css');
        AssetsManager::addJs('/assets/js/app.js');
    }
}