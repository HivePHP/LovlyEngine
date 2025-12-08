<?php
declare(strict_types=1);

namespace HivePHP\Validation\Rules;

interface RuleInterface
{
    public function validate(string $field, mixed $value, array $data): bool;
    public function message(string $field): string;
}