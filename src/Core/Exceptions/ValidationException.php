<?php

namespace MIMS\Core\Exceptions;

use Exception;

/**
 * Validation Exception
 * Thrown when validation fails
 */
class ValidationException extends Exception
{
    private array $errors;

    public function __construct(string $message = "", array $errors = [], int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
