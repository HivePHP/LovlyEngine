<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use HivePHP\Database;
use HivePHP\Configs;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $dbConfig = Configs::get('database')
            ?? throw new \RuntimeException("Database config not found!");

        $this->container->set('db', new Database($dbConfig));
    }
}
