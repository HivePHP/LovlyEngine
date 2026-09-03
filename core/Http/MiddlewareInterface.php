<?php

declare(strict_types=1);

namespace HivePHP\Http;

use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): void;
}
