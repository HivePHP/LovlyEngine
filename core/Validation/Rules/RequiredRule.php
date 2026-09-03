<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Validation\Rules;
use HivePHP\Validation\RuleInterface;

final class RequiredRule implements RuleInterface {
    public function check(mixed $value, array $data): bool {
        return !($value === null || $value === '');
    }
    public function message(string $field): string {
        return "{$field} обязательно";
    }
}
