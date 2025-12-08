<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class MinRule implements RuleInterface
{
    public function __construct(private int $min) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        return mb_strlen((string)$value) >= $this->min;
    }

    public function message(string $field): string
    {
        return "Минимальная длина поля {$field} — {$this->min}.";
    }
}
