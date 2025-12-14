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

    /* GET */
    $routes->addRoute('GET', '/', ['App\Controllers\HomeController', 'showLogin']);
    $routes->addRoute('GET', '/reg', ['App\Controllers\HomeController', 'showRegister']);
    $routes->addRoute('GET', '/id{id:\d+}', ['App\Controllers\ProfileController', 'show']);

    /* POST */
    $routes->addRoute('POST', '/login', ['App\Controllers\AuthController', 'login']);
    $routes->addRoute('POST', '/reg', ['App\Controllers\AuthController', 'register']);
};
