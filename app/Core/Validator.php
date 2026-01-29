<?php

namespace App\Core;

class Validator
{
    private array $errors = [];

    /**
     * Validate required field
     */
    public function required(string $field, mixed $value): bool
    {
        if (empty(trim((string)$value))) {
            $this->addError($field, "$field is required");
            return false;
        }
        return true;
    }

    /**
     * Validate email format
     */
    public function email(string $field, string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "$field must be a valid email address");
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, string $value, int $length): bool
    {
        if (strlen($value) < $length) {
            $this->addError($field, "$field must be at least $length characters");
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, string $value, int $length): bool
    {
        if (strlen($value) > $length) {
            $this->addError($field, "$field must not exceed $length characters");
            return false;
        }
        return true;
    }

    /**
     * Validate that two fields match
     */
    public function matches(string $field, string $value1, string $value2, string $otherField = 'confirmation'): bool
    {
        if ($value1 !== $value2) {
            $this->addError($field, "$field and $otherField do not match");
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     */
    public function numeric(string $field, mixed $value): bool
    {
        if (!is_numeric($value)) {
            $this->addError($field, "$field must be numeric");
            return false;
        }
        return true;
    }

    /**
     * Validate phone number format
     */
    public function phone(string $field, string $value): bool
    {
        // Simple phone validation - accepts 10+ digits
        if (!preg_match('/^[\d\s\-\+\(\)]{10,}$/', $value)) {
            $this->addError($field, "$field must be a valid phone number");
            return false;
        }
        return true;
    }

    /**
     * Validate URL format
     */
    public function url(string $field, string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "$field must be a valid URL");
            return false;
        }
        return true;
    }

    /**
     * Check if value exists in array
     */
    public function inArray(string $field, mixed $value, array $allowed): bool
    {
        if (!in_array($value, $allowed, true)) {
            $this->addError($field, "$field contains an invalid value");
            return false;
        }
        return true;
    }

    /**
     * Add custom error
     */
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Check if validation passed (no errors)
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed (has errors)
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get first error
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }

    /**
     * Clear all errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}
