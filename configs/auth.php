<?php
declare(strict_types=1);

return [
    // lifetime сессии (сек)
    'session_lifetime' => 60 * 60 * 24, // 1 day for session entry in DB (php session lifetime can be smaller)

    // Remember me
    'remember_lifetime' => 60 * 60 * 24 * 30, // 30 days

    // Cookie name для remember me
    'cookie_name' => 'remember_me',

    // Если пользуешься cookie manager — конфиг cookie берётся из config/cookie.php
];
