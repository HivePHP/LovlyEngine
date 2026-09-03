<?php

declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    return HivePHP\Support\Dotenv::get($key, $default);
}