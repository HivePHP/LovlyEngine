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
use HivePHP\Security\CsrfToken;

final class VerifyCsrfToken implements MiddlewareInterface
{
    public function __construct(
        private readonly Response $response
    ) {}

    public function handle(Request $request, Closure $next): void
    {
        if ($request->getMethod() === 'POST') {
            $token = $request->header('X-CSRF-Token');

            if ($token === null && isset($_POST['csrf_token'])) {
                $token = $_POST['csrf_token'];
            }

            if ($token === null && $_SERVER['CONTENT_TYPE'] !== null && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
                $rawBody = file_get_contents('php://input');
                if ($rawBody !== '' && $rawBody !== false) {
                    $data = json_decode($rawBody, true);
                    if (is_array($data)) {
                        $token = $data['csrf_token'] ?? null;
                    }
                }
            }

            if ($token === null) {
                $this->response->json([
                    'status'  => 'error',
                    'message' => 'CSRF token missing',
                ], 403);
                return;
            }

            if (!CsrfToken::validate($token)) {
                $this->response->json([
                    'status'  => 'error',
                    'message' => 'CSRF token invalid',
                ], 403);
                return;
            }
        }

        $next($request);
    }
}
