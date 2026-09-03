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

final class MinRule implements RuleInterface {
    public function __construct(private int $min) {}
    public function check(mixed $value, array $data): bool {
        return is_string($value)
            ? mb_strlen($value) >= $this->min
            : $value >= $this->min;
    }
    public function message(string $field): string {
        return "{$field} минимум {$this->min}";
    }
}
