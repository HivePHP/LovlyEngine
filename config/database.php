<?php

declare(strict_types=1);

return [
    'host'    => env('DB_HOST', 'MariaDB-11.8'),
    'dbname'  => env('DB_DATABASE', 'hivephp'),
    'user'    => env('DB_USERNAME', 'root'),
    'pass'    => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];