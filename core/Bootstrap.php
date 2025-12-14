<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

namespace HivePHP;


class Bootstrap
{
    private Database $db;

    public function run(): void
    {
        $this->bootDataBase();
        $this->bootView();
        $this->bootRouter();
    }

    private function bootDataBase(): void
    {
        $this->db = new Database(Config::load('database'));
    }

    private function bootView(): void
    {
        View::init(
            Config::load('view')
        );
//        AssetsManager::addCss('/assets/css/layout.css');
//        AssetsManager::addJs('/assets/js/app.js');
    }

    private function bootRouter(): void
    {
        $router = new Router(
            Config::load('app'),
            $this->db
        );
        $router->dispatch();
    }
}