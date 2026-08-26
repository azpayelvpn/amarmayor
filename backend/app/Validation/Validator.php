<?php

declare(strict_types=1);

namespace AmarMayor\Validation;

use AmarMayor\Support\Translator;

/**
 * Lightweight Explicit Form & API Validator.
 */
class Validator
{
    private array $data;
    private array $rules;
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

    public function validate(): array
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $ruleName = trim($ruleName);
                $valid = $this->evaluateRule($field, $value, $ruleName, $params);

                if (!$valid) {
                    $this->addError($field, $ruleName, $params);
                    break; // Move to next field upon first rule failure
                }
            }
        }

        return $this->errors;
    }

    public function passes(): bool
    {
        return empty($this->validate());
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    private function evaluateRule(string $field, mixed $value, string $rule, array $params): bool
    {
        // Null or empty handling (unless rule is 'required')
        if ($rule !== 'required' && ($value === null || $value === '')) {
            return true;
        }

        return match ($rule) {
            'required' => $value !== null && $value !== '' && (is_array($value) ? count($value) > 0 : true),
            'string' => is_string($value),
            'integer', 'int' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'numeric' => is_numeric($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'phone' => is_string($value) && preg_match('/^(?:\+?880|0)1[3-9]\d{8}$/', preg_replace('/[\s\-]/', '', $value)) === 1,
            'boolean', 'bool' => in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true),
            'min' => is_numeric($value) ? ((float)$value >= (float)$params[0]) : (mb_strlen((string)$value) >= (int)$params[0]),
            'max' => is_numeric($value) ? ((float)$value <= (float)$params[0]) : (mb_strlen((string)$value) <= (int)$params[0]),
            'in' => in_array((string)$value, $params, true),
            'date' => is_string($value) && strtotime($value) !== false,
            default => true,
        };
    }

    private function addError(string $field, string $rule, array $params): void
    {
        $message = Translator::get("validation.{$rule}", [
            'field' => Translator::get("validation.fields.{$field}", [], null) !== "validation.fields.{$field}" 
                ? Translator::get("validation.fields.{$field}") 
                : $field,
            'param' => implode(', ', $params),
            'min' => $params[0] ?? '',
            'max' => $params[0] ?? '',
        ]);

        $this->errors[$field][] = $message;
    }
}
