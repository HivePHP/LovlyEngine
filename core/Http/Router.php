<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Http;

use App\Http\Kernel;
use Closure;
use HivePHP\Support\Container;
use RuntimeException;
use Throwable;

final class Router
{
    private array $routes = [];
    private array $pendingMiddleware = [];

    public function __construct(
        private Container $container,
        private Kernel $kernel
    ) {}

    /* ===================== */
    /* Fluent middleware     */
    /* ===================== */

    public function middleware(string ...$middleware): self
    {
        foreach ($middleware as $alias) {
            $this->pendingMiddleware[] = $alias;
        }

        return $this;
    }

    /* ===================== */
    /* Routes                */
    /* ===================== */

    public function get(string $pattern, callable|array $handler): Route
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): Route
    {
        return $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable|array $handler): Route
    {
        $pattern = rtrim($pattern, '/') ?: '/';

        $regex = preg_replace_callback(
            '#\{(\w+)\}#',
            static fn($m) => '(?P<' . $m[1] . '>\d+)',
            $pattern
        );

        $route = new Route(
            $method,
            '#^' . $regex . '$#',
            $handler,
            $this->pendingMiddleware
        );

        $this->pendingMiddleware = [];
        $this->routes[] = $route;

        return $route;
    }

    /* ===================== */
    /* Dispatch              */
    /* ===================== */

    public function dispatch(Request $request, Response $response): void
    {
        $uri    = rtrim($request->getPath(), '/') ?: '/';
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            if (!preg_match($route->pattern, $uri, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn($k) => is_string($k),
                ARRAY_FILTER_USE_KEY
            );

            try {
                $this->runPipeline($request, $route, $params);
            } catch (HttpException $e) {
                $response->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $e->status);
            } catch (Throwable $e) {
                $log = sprintf(
                    "[%s] %s in %s:%d\n%s\n\n",
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                );
                @file_put_contents(BASE_PATH . '/storage/logs/error.log', $log, FILE_APPEND);

                $response->json([
                    'status'  => 'error',
                    'message' => 'Internal server error',
                ], 500);
            }
            return;
        }

        $this->abort404();
    }

    /* ===================== */
    /* Pipeline              */
    /* ===================== */

    private function runPipeline(Request $request, Route $route, array $params): void
    {
        $middlewareClasses = $this->kernel->resolveMiddleware(
            array_merge($this->kernel->getGlobalMiddleware(), $route->middleware)
        );

        $destination = function (Request $request) use ($route, $params) {
            $this->callHandler($route->handler, $params);
        };

        $pipeline = $this->buildPipeline($middlewareClasses, $destination);

        $pipeline($request);
    }

    private function buildPipeline(array $middlewareClasses, Closure $destination): Closure
    {
        $pipeline = $destination;

        foreach (array_reverse($middlewareClasses) as $className) {
            $middleware = $this->container->get($className);

            if (!$middleware instanceof MiddlewareInterface) {
                throw new RuntimeException(
                    "{$className} must implement " . MiddlewareInterface::class
                );
            }

            $next = $pipeline;

            $pipeline = function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        return $pipeline;
    }

    /* ===================== */
    /* Helpers               */
    /* ===================== */

    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        [$class, $method] = $handler;

        $controller = $this->container->get($class);
        call_user_func_array([$controller, $method], $params);
    }

    private function abort404(): void
    {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }
}
