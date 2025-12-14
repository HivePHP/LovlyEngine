<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */

declare(strict_types=1);

use FastRoute\ConfigureRoutes;

return function (ConfigureRoutes $routes): void {

    $routes->addRoute('GET', '/', ['App\Controllers\HomeController', 'showLogin']);

    // пример
    // $routes->addRoute('GET', '/login', ['App\Controllers\AuthController', 'login']);
};
