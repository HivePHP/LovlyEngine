<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

use HivePHP\Validation\RuleInterface;

final class EmailRule implements RuleInterface {
    public function check(mixed $value, array $data): bool {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    public function message(string $field): string {
        return "Некорректный email";
    }
}
