<?php

namespace MIMS\Core\Validation;

use MIMS\Core\Exceptions\ValidationException;

/**
 * Validator Class
 * Handles input validation with various rules
 */
class Validator
{
    private array $errors = [];

    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules): array
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->validateField($field, $value, $rule);
            }
        }

        return $this->errors;
    }

    /**
     * Validate single field
     */
    private function validateField(string $field, $value, string $rule): void
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== 0) {
                    $this->addError($field, 'This field is required');
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    $this->addError($field, 'This field must be a string');
                }
                break;

            case 'integer':
                if (!is_int($value) && !ctype_digit($value)) {
                    $this->addError($field, 'This field must be an integer');
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, 'This field must be numeric');
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'This field must be a valid email address');
                }
                break;

            case 'date':
                if (!$this->isValidDate($value)) {
                    $this->addError($field, 'This field must be a valid date');
                }
                break;

            case 'min':
                if (is_numeric($value) && $value < $ruleValue) {
                    $this->addError($field, "This field must be at least {$ruleValue}");
                } elseif (is_string($value) && strlen($value) < $ruleValue) {
                    $this->addError($field, "This field must be at least {$ruleValue} characters");
                }
                break;

            case 'max':
                if (is_numeric($value) && $value > $ruleValue) {
                    $this->addError($field, "This field must not exceed {$ruleValue}");
                } elseif (is_string($value) && strlen($value) > $ruleValue) {
                    $this->addError($field, "This field must not exceed {$ruleValue} characters");
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!in_array($value, $allowedValues)) {
                    $this->addError($field, "This field must be one of: " . implode(', ', $allowedValues));
                }
                break;

            case 'regex':
                if (!preg_match($ruleValue, $value)) {
                    $this->addError($field, 'This field format is invalid');
                }
                break;

            case 'unique':
                // This would require database validation
                // For now, we'll skip this validation
                break;
        }
    }

    /**
     * Add validation error
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Check if date is valid
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get first error for field
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitize input data
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
