<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP;

use HivePHP\Support\Container;

class Bootstrap
{
    protected array $providers = [
        \HivePHP\Providers\DbServiceProvider::class,
        \HivePHP\Providers\AssetsServiceProvider::class,
        \HivePHP\Providers\ViewServiceProvider::class,
        \HivePHP\Providers\HttpServiceProvider::class,
        \HivePHP\Providers\RouterServiceProvider::class
    ];

    protected array $instances = [];

    public function __construct(
        protected Container $container
    ){}

    public function run(): void
    {
        $this->registerProviders();
        $this->bootProviders();
    }

    protected function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->container);
            $provider->register($this->container);
            $this->instances[] = $provider;
        }
    }

    protected function bootProviders(): void
    {
        foreach ($this->instances as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot($this->container);
            }
        }
    }
}