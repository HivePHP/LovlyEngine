<?php

declare(strict_types=1);

namespace HivePHP\Validation\Rules;

use HivePHP\Validation\RuleInterface;

final class NullableRule implements RuleInterface
{
    public function check(mixed $value, array $data): bool
    {
        return $value === null || $value === '';
    }

    public function message(string $field): string
    {
        return "{$field} не может быть пустым";
    }
}
