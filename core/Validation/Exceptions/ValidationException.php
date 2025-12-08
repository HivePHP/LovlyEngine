<?php
declare(strict_types=1);

namespace HivePHP\Validation\Exceptions;

class ValidationException extends \Exception
{
    public function __construct(
        private array $errors,
        string $message = "Ошибка валидации"
    ) {
        parent::__construct($message);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
