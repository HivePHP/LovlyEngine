<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

class DateValidRule implements RuleInterface
{
    public function __construct(
        private string $dayField,
        private string $monthField,
        private string $yearField
    ) {}

    public function validate(string $field, mixed $value, array $data): bool
    {
        $d = (int)($data[$this->dayField] ?? 0);
        $m = (int)($data[$this->monthField] ?? 0);
        $y = (int)($data[$this->yearField] ?? 0);

        return checkdate($m, $d, $y);
    }

    public function message(string $field): string
    {
        return "Дата некорректна.";
    }
}
