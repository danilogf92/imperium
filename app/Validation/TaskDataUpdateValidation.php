<?php

namespace App\Validation;

class TaskDataUpdateValidation
{
    public static function rules(): array
    {
        return [
            'editData.percentage' => ['required', 'integer', 'between:0,100'],
        ];
    }

    public static function attributes(): array
    {
        return [
            'editData.percentage' => 'percentage',
        ];
    }
}
