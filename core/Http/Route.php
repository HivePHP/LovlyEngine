<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Http;

final class Route
{
    public function __construct(
        public string $method,
        public string $pattern,
        public mixed $handler,
        public array $middleware = []
    ) {}

    public function middleware(string|array $middleware): self
    {
        foreach ((array)$middleware as $m) {
            $this->middleware[] = $m;
        }

        return $this;
    }
}
