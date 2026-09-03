<?php

declare(strict_types=1);

namespace HivePHP\Validation;

final class Validator
{
    private array $errors = [];
    private array $clean  = [];

    private function __construct(
        private array $data,
        private array $rules
    ) {}

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): self
    {
        foreach ($this->rules as $field => $ruleString) {
            $value  = $this->data[$field] ?? null;
            $parsed = RuleParser::parse($ruleString);

            $isNullable = false;
            foreach ($parsed as $rule) {
                if ($rule instanceof Rules\NullableRule) {
                    $isNullable = true;
                    break;
                }
            }

            if ($isNullable && ($value === null || $value === '')) {
                $this->clean[$field] = '';
                continue;
            }

            foreach ($parsed as $rule) {
                if ($rule instanceof Rules\NullableRule) {
                    continue;
                }

                if (!$rule->check($value, $this->data)) {
                    $this->errors[$field] = $rule->message($field);
                    break;
                }
            }

            if (!isset($this->errors[$field]) && $value !== null) {
                $this->clean[$field] = is_string($value)
                    ? trim($value)
                    : $value;
            }
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function clean(): array
    {
        return $this->clean;
    }
}
