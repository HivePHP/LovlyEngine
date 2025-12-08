<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class EmailRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $data): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function message(string $field): string
    {
        return "Неверный email.";
    }
}
