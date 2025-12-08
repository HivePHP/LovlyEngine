<?php
declare(strict_types=1);

namespace App\Exceptions;

class RegistrationException extends \Exception
{
    protected array $errors = [];

    public function __construct(array|string $errors)
    {
        if (is_string($errors)) {
            // Ошибка общего типа
            $this->errors = ['server' => $errors];
        } else {
            // Ошибки по полям
            $this->errors = $errors;
        }

        parent::__construct("Registration error");
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}