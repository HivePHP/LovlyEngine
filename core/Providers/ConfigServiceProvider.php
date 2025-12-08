<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Configs;

class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Configs::load(ROOT . '/configs');
        $this->container->set('configs', Configs::all());
    }
}