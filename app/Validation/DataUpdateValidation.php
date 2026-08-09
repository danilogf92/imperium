<?php

namespace App\Validation;

class DataUpdateValidation
{
    private const TEXT_FIELDS = [
        'area',
        'group_1',
        'group_2',
        'general_classification',
        'item_type',
        'unit',
        'stage',
        'supplier',
        'code',
        'order_no',
        'input_num',
    ];

    private const NUMERIC_FIELDS = [
        'qty',
        'unit_price',
        'global_price',
        'global_price_euros',
        'real_value',
        'real_value_euros',
        'percentage',
        'executed_dollars',
        'executed_euros',
        'booked',
        'booked_euros',
    ];

    public static function rules(): array
    {
        $rules = [
            'editData' => ['required', 'array'],
            'editData.description' => ['nullable', 'string', 'max:10000'],
            'editData.observations' => ['nullable', 'string', 'max:10000'],
        ];

        foreach (self::TEXT_FIELDS as $field) {
            $rules["editData.{$field}"] = ['nullable', 'string', 'max:255'];
        }

        foreach (self::NUMERIC_FIELDS as $field) {
            $rules["editData.{$field}"] = ['required', 'numeric'];
        }

        $rules['editData.percentage'][] = 'between:0,100';
        $rules['editData.order_year'] = ['nullable', 'integer', 'between:2000,2100', 'required_with:editData.order_no'];

        return $rules;
    }

    public static function attributes(): array
    {
        return collect(array_merge(
            self::TEXT_FIELDS,
            self::NUMERIC_FIELDS,
            ['description', 'observations', 'order_year']
        ))->mapWithKeys(
            fn (string $field) => [
                "editData.{$field}" => (string) str($field)->replace('_', ' ')->title(),
            ]
        )->all();
    }
}
