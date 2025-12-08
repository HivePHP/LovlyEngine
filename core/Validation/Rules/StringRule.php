<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class StringRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $data): bool
    {
        return is_string($value);
    }

    public function message(string $field): string
    {
        return "Поле {$field} должно быть строкой.";
    }
}
