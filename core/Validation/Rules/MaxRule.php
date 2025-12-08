<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class MaxRule implements RuleInterface
{
    public function __construct(private int $max)
    {
    }

    public function validate(string $field, mixed $value, array $data): bool
    {
        return mb_strlen((string)$value) <= $this->max;
    }

    public function message(string $field): string
    {
        return "Максимальная длина поля {$field} — {$this->max}.";
    }
}