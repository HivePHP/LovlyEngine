<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class InRule implements RuleInterface
{
    public function __construct(
        private array $allowed
    ) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return in_array($value, $this->allowed, true);
    }

    public function message(string $field): string
    {
        $values = implode(', ', $this->allowed);
        return "Поле {$field} должно быть одним из: {$values}.";
    }
}
