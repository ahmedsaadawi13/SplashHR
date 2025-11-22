<?php
// FILE: /app/core/Validator.php

/**
 * Input Validation Class
 * Handles input validation
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Validator {

    private $errors = [];

    /**
     * Validate required field
     * @param string $field
     * @param mixed $value
     * @param string $label
     */
    public function required($field, $value, $label = null) {
        $label = $label ?? $field;
        if (empty($value) && $value !== '0') {
            $this->errors[$field] = "$label is required";
        }
    }

    /**
     * Validate email
     * @param string $field
     * @param string $value
     */
    public function email($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Invalid email format";
        }
    }

    /**
     * Validate minimum length
     * @param string $field
     * @param string $value
     * @param int $min
     * @param string $label
     */
    public function minLength($field, $value, $min, $label = null) {
        $label = $label ?? $field;
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = "$label must be at least $min characters";
        }
    }

    /**
     * Validate maximum length
     * @param string $field
     * @param string $value
     * @param int $max
     * @param string $label
     */
    public function maxLength($field, $value, $max, $label = null) {
        $label = $label ?? $field;
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = "$label must not exceed $max characters";
        }
    }

    /**
     * Validate numeric
     * @param string $field
     * @param mixed $value
     * @param string $label
     */
    public function numeric($field, $value, $label = null) {
        $label = $label ?? $field;
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "$label must be numeric";
        }
    }

    /**
     * Validate date
     * @param string $field
     * @param string $value
     * @param string $format
     */
    public function date($field, $value, $format = 'Y-m-d') {
        if (!empty($value)) {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field] = "Invalid date format";
            }
        }
    }

    /**
     * Validate in array
     * @param string $field
     * @param mixed $value
     * @param array $options
     * @param string $label
     */
    public function inArray($field, $value, $options, $label = null) {
        $label = $label ?? $field;
        if (!empty($value) && !in_array($value, $options)) {
            $this->errors[$field] = "Invalid $label value";
        }
    }

    /**
     * Check if validation passed
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get validation errors
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get first error
     * @return string|null
     */
    public function firstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
