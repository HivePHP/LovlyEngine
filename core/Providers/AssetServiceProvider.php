<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\AssetManager;

class AssetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->set('assets', new AssetManager());
    }
}
