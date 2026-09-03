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

final class Guest implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Response $response
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        if ($this->auth->check()) {
            $user = $this->auth->user();
            $this->response->redirect('/id' . $user['id']);
            return;
        }

        $next($request);
    }
}
