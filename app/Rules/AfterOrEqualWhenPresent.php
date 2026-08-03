<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class AfterOrEqualWhenPresent implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function __construct(private readonly string $otherField) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $otherValue = data_get($this->data, $this->otherField);

        if (blank($value) || blank($otherValue)) {
            return;
        }

        $timestamp = strtotime((string) $value);
        $otherTimestamp = strtotime((string) $otherValue);

        if ($timestamp !== false && $otherTimestamp !== false && $timestamp < $otherTimestamp) {
            $fail('The close date must be after or equal to the approve date.');
        }
    }
}
