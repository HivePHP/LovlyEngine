<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    private Dispatcher $dispatcher;

    public function __construct(
        private readonly array $config,
        private readonly Database $db
    )
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $routes = require PATH . '/routes/web.php';
            $routes($r);
        });
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {

            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 Not Found';
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo '405 Method Not Allowed';
                break;

            case Dispatcher::FOUND:
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                $controller = new $class($this->config, $this->db);
                call_user_func_array([$controller, $method], $vars);
                break;
        }
    }
}
