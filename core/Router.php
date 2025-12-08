<?php
declare(strict_types=1);

namespace HivePHP;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var array|array[]
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    /**
     * @var array
     */
    private array $currentMiddleware = []; // Для цепочки middleware

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function middleware(string $mode): self
    {
        $this->currentMiddleware[] = $mode;
        return $this;
    }

    /**
     * @param string $uri
     * @param array $action
     * @return void
     */
    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][] = [$uri, $action, $this->currentMiddleware];
        $this->currentMiddleware = []; // сброс после добавления
    }

    /**
     * @param string $uri
     * @param array $action
     * @return void
     */
    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][] = [$uri, $action, $this->currentMiddleware];
        $this->currentMiddleware = []; // сброс после добавления
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $method => $items) {
                foreach ($items as [$uri, $action, $middleware]) {
                    $r->addRoute($method, $uri, [$action, $middleware]);
                }
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->notFound();
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->methodNotAllowed();
                break;

            case \FastRoute\Dispatcher::FOUND:
                [$action, $middlewareModes] = $routeInfo[1];
                $vars = $routeInfo[2];

                // Выполняем middleware
                foreach ($middlewareModes as $mode) {
                    $instance = new \App\Middleware\AuthMiddleware($this->container, $mode);
                    if ($instance->handle() === false) return;
                }

                // Выполняем контроллер
                $this->run($action, $vars);
                break;
        }
    }

    /**
     * @param array $action
     * @param array $params
     * @return void
     */
    private function run(array $action, array $params): void
    {
        [$controller, $method] = $action;
        $controllerObj = new $controller($this->container);
        call_user_func_array([$controllerObj, $method], $params);
    }

    /**
     * @return void
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo "404 Not Found";
    }

    /**
     * @return void
     */
    private function methodNotAllowed(): void
    {
        http_response_code(405);
        echo "405 Method Not Allowed";
    }
}
