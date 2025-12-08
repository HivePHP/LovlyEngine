<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\AssetManager;
use HivePHP\Container;

abstract class ServiceProvider
{
    protected AssetManager $assets;
    public function __construct(protected Container $container)
    {

    }

    protected function assets(): AssetManager
    {
        return $this->container->get('assets');
    }

    abstract public function register(): void;


}