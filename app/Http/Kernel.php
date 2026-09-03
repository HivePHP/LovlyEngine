<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\Auth;
use App\Http\Middleware\Guest;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\WebAssets;

final class Kernel
{
    protected array $middleware = [
        ThrottleRequests::class,
        VerifyCsrfToken::class,
    ];

    protected array $middlewareGroups = [
        'web' => [
            WebAssets::class,
        ],
    ];

    protected array $middlewareAliases = [
        'auth'      => Auth::class,
        'guest'     => Guest::class,
        'web'       => WebAssets::class,
        'csrf'      => VerifyCsrfToken::class,
        'throttle'  => ThrottleRequests::class,
    ];

    public function getGlobalMiddleware(): array
    {
        return $this->middleware;
    }

    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }

    public function getMiddlewareAliases(): array
    {
        return $this->middlewareAliases;
    }

    public function resolveMiddleware(array $names): array
    {
        $resolved = [];

        foreach ($names as $name) {
            if (isset($this->middlewareGroups[$name])) {
                foreach ($this->middlewareGroups[$name] as $groupMember) {
                    $resolved[] = $this->resolveAlias($groupMember);
                }
            } else {
                $resolved[] = $this->resolveAlias($name);
            }
        }

        return array_values(array_unique($resolved));
    }

    private function resolveAlias(string $alias): string
    {
        if (isset($this->middlewareAliases[$alias])) {
            return $this->middlewareAliases[$alias];
        }

        return $alias;
    }
}
