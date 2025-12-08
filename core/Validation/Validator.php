<?php
declare(strict_types=1);

namespace HivePHP\Validation;

use HivePHP\Validation\Rules\RuleInterface;
use HivePHP\Validation\Exceptions\ValidationException;

class Validator
{
    private array $data;
    private array $rules;
    private array $clean = [];
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): self
    {
        foreach ($this->rules as $field => $ruleList) {

            $value = $this->sanitize($this->data[$field] ?? null);

            $rules = is_array($ruleList)
                ? $ruleList
                : explode('|', $ruleList);

            foreach ($rules as $rule) {

                if ($rule instanceof RuleInterface) {
                    $r = $rule;
                } else {
                    $r = $this->parseRuleString($rule);
                }

                if (!$r->validate($field, $value, $this->data)) {
                    $this->errors[$field][] = $r->message($field);
                    break;
                }
            }

            if (!isset($this->errors[$field])) {
                $this->clean[$field] = $value;
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function clean(): array
    {
        return $this->clean;
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) return trim($value);
        return $value;
    }

    private function parseRuleString(string $rule): RuleInterface
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
        } else {
            $name = $rule;
            $param = null;
        }

        return match ($name) {
            'required'      => new Rules\RequiredRule(),
            'string'        => new Rules\StringRule(),
            'integer'       => new Rules\IntegerRule(),
            'email'         => new Rules\EmailRule(),
            'min'           => new Rules\MinRule((int)$param),
            'max'           => new Rules\MaxRule((int)$param),
            'same'          => new Rules\SameRule($param),
            'alpha'         => new Rules\AlphaRule(),
            'alpha_space'   => new Rules\AlphaSpaceRule(),
            'in'            => new Rules\InRule(explode(',', $param)),
            'date_valid'    => new Rules\DateValidRule(...explode(',', $param)),
            default         => throw new \RuntimeException("Unknown validation rule: {$rule}")
        };
    }
}
