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

use Closure;
use HivePHP\Http\MiddlewareInterface;
use HivePHP\Http\Request;
use HivePHP\Http\Response;
use HivePHP\Security\RateLimiter;

final class ThrottleRequests implements MiddlewareInterface
{
    public function __construct(
        private readonly Response $response
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        $key = 'global:' . $request->ip();

        if (!RateLimiter::check($key, 120, 60)) {
            $remaining = RateLimiter::remaining($key, 120, 60);

            $this->response->json([
                'status'  => 'error',
                'message' => 'Too many requests. Please try again later.',
            ], 429);
            return;
        }

        $next($request);
    }
}
