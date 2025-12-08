<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class SameRule implements RuleInterface
{
    public function __construct(private string $other) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return ($data[$this->other] ?? null) === $value;
    }

    public function message(string $field): string
    {
        return "Поле {$field} должно совпадать с {$this->other}.";
    }
}
