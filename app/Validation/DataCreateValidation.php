<?php

namespace App\Validation;

class DataCreateValidation
{
    public static function rules(): array
    {
        return DataUpdateValidation::rules();
    }

    public static function attributes(): array
    {
        return DataUpdateValidation::attributes();
    }
}
