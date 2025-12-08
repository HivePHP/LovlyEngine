<?php
declare(strict_types=1);

return [

    // Префикс для всех кук
    'prefix' => 'hive_',

    // Домен куки (null = автоматом текущий)
    'domain' => null,

    // Путь
    'path' => '/',

    // Дни жизни куки
    'lifetime' => 30,

    // Только HTTPS
    'secure' => false,

    // Только HTTP (JS не прочтёт)
    'http_only' => true,

    // SameSite: Lax / Strict / None
    'same_site' => 'Lax',

    // Шифровать значения
    'encrypt' => true,

    // Ключ шифрования (32 байта)
    'key' => '8vdXC3EVAv-qTS0-0cZw2gbSHLrpEcXM',
];
