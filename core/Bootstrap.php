<?php
declare(strict_types=1);

namespace HivePHP;

use HivePHP\Providers\{
    AuthServiceProvider,
    ConfigServiceProvider,
    CookieServiceProvider,
    DatabaseServiceProvider,
    AssetServiceProvider,
    ViewServiceProvider,
    ModelServiceProvider,
    RouteServiceProvider
};

class Bootstrap
{
    protected array $providers = [
        ConfigServiceProvider::class,
        DatabaseServiceProvider::class,
        AssetServiceProvider::class,
        ModelServiceProvider::class,
        RouteServiceProvider::class,
        CookieServiceProvider::class,
        AuthServiceProvider::class,
        ViewServiceProvider::class,
    ];

    protected array $instances = [];

    public function __construct(protected Container $container)
    {
        if (!defined('ROOT')) {
            throw new \RuntimeException("ROOT constant is not defined.");
        }
    }

    public function run(): void
    {
        require_once ROOT . '/app/Support/helpers.php';

        $this->registerProviders();
        $this->bootProviders();
    }

    protected function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->container);
            $provider->register();
            $this->instances[] = $provider;
        }
    }

    protected function bootProviders(): void
    {
        foreach ($this->instances as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
}
