<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class AlphaRule implements RuleInterface
{
    public function validate(string $field, mixed $value, array $data): bool
    {
        return is_string($value)
            && preg_match('/^[a-zA-Zа-яА-ЯёЁ\-]+$/u', $value);
    }

    public function message(string $field): string
    {
        return "Поле {$field} может содержать только буквы и дефис.";
    }
}
