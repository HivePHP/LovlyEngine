<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CaptchaService;

/**
 * Serves the CAPTCHA PNG for the registration form.
 */
final class CaptchaController
{
    public function __construct(
        private readonly CaptchaService $captcha
    ) {}

    public function image(): void
    {
        // Never let the browser/proxies cache a CAPTCHA image — every request
        // must show a (possibly different) code bound to the current session.
        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->captcha->generate();
        exit;
    }
}
