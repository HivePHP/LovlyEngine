<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use HivePHP\Http\MiddlewareInterface;
use HivePHP\Http\Request;
use HivePHP\Http\Response;

final class Auth implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Response $response
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        if (!$this->auth->check()) {
            if ($this->expectsJson($request)) {
                $this->response->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized',
                ], 401);
                return;
            }

            $this->response->redirect('/');
            return;
        }

        $next($request);
    }

    private function expectsJson(Request $request): bool
    {
        if (str_starts_with($request->getPath(), '/api')) {
            return true;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($contentType, 'application/json')
            || strtolower($requestedWith) === 'xmlhttprequest';
    }
}
