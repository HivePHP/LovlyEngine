<?php

declare(strict_types=1);

return [
    'cache'             => BASE_PATH . '/' . env('VIEW_CACHE', 'storage/cache/view'),
    'auto_reload'       => filter_var(env('VIEW_AUTO_RELOAD', 'true'), FILTER_VALIDATE_BOOLEAN),
    'debug'             => filter_var(env('VIEW_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'strict_variables'  => filter_var(env('VIEW_STRICT_VARIABLES', 'true'), FILTER_VALIDATE_BOOLEAN),
];