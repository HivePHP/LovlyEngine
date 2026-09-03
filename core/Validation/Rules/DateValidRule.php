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

final class DateValidRule implements RuleInterface {
    public function __construct(private array $fields) {}
    public function check(mixed $value, array $data): bool {
        return checkdate(
            (int)$data[$this->fields[1]],
            (int)$data[$this->fields[0]],
            (int)$data[$this->fields[2]]
        );
    }
    public function message(string $field): string {
        return "Некорректная дата";
    }
}
