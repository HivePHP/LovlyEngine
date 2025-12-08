<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class RequiredRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $data): bool
    {
        return !($value === null || $value === '' || (is_array($value) && empty($value)));
    }

    public function message(string $field): string
    {
        return "Поле {$field} обязательно.";
    }
}