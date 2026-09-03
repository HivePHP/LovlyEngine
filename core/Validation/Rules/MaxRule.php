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

final class MaxRule implements RuleInterface {
    public function __construct(private int $max) {}
    public function check(mixed $value, array $data): bool {
        return is_string($value)
            ? mb_strlen($value) <= $this->max
            : $value <= $this->max;
    }
    public function message(string $field): string {
        return "{$field} максимум {$this->max}";
    }
}
