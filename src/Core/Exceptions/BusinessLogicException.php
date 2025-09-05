<?php

namespace MIMS\Core\Exceptions;

use Exception;

/**
 * Business Logic Exception
 * Thrown when business rules are violated
 */
class BusinessLogicException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
