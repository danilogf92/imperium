<?php

namespace App\Validation;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Models\ProjectRateSetting;
use App\Rules\AfterOrEqualWhenPresent;
use Illuminate\Validation\Rule;

class ProjectCreateValidation
{
    public static function rules(): array
    {
        $rateSettings = ProjectRateSetting::current();

        return [
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'order' => [
                'required',
                'string',
                'regex:/^\d+[a-z]*$/i',
                'max:20',
            ],

            'pda_code' => self::pdaRules(),

            'rate' => [
                'required',
                'numeric',
                'min:' . $rateSettings->min_rate,
                'max:' . $rateSettings->max_rate,
            ],

            'state' => [
                'required',
                'string',
                Rule::in(ProjectStateEnum::values()),
            ],

            'investments' => [
                'required',
                'string',
                Rule::in(InvestmentEnum::values()),
            ],

            'justification' => [
                'required',
                'string',
                Rule::in(ProjectJustificationEnum::values()),
            ],

            'classification_of_investments' => [
                'required',
                'string',
                Rule::in(InvestmentClassificationEnum::values()),
            ],

            'forecast_start_date' => [
                'required',
                'date',
                'before_or_equal:forecast_end_date',
            ],

            'forecast_end_date' => [
                'required',
                'date',
                'after_or_equal:forecast_start_date',
            ],

            'quartile_date' => [
                'nullable',
                'date',
            ],

            'approve_date' => [
                'nullable',
                'date',
                'after_or_equal:forecast_start_date',
            ],

            'close_date' => [
                'nullable',
                'date',
                'after_or_equal:forecast_start_date',
                new AfterOrEqualWhenPresent('approve_date'),
            ],

            'data_uploaded' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public static function pdaRules(?int $ignoreProjectId = null): array
    {
        $uniquePdaCode = Rule::unique('projects', 'pda_code');

        if ($ignoreProjectId) {
            $uniquePdaCode->ignore($ignoreProjectId);
        }

        return [
            'required',
            'string',
            'regex:/^.+_.+$/',
            'max:255',
            $uniquePdaCode,
        ];
    }

    public static function messages(): array
    {
        $rateSettings = ProjectRateSetting::current();

        return [
            'company_id.required' =>
            'The company is required.',

            'company_id.integer' =>
            'The selected company is invalid.',

            'company_id.exists' =>
            'The selected company does not exist.',

            'name.required' =>
            'The project name is required.',

            'name.string' =>
            'The project name must be valid text.',

            'name.max' =>
            'The project name may not be greater than 255 characters.',

            'order.required' =>
            'The project order is required.',

            'order.regex' =>
            'The project order must start with a number and may end with letters, for example 10, 10a, or 10b.',

            'order.unique' =>
            'This project order is already in use for the selected plant.',

            'pda_code.required' =>
            'The PDA code is required.',

            'pda_code.string' =>
            'The PDA code must be valid text.',

            'pda_code.regex' =>
            'The PDA code is required.',

            'pda_code.max' =>
            'The PDA code may not be greater than 255 characters.',

            'pda_code.unique' =>
            'The PDA code is already registered.',

            'rate.required' =>
            'The rate is required.',

            'rate.numeric' =>
            'The rate must be numeric.',

            'rate.min' =>
            "The rate must be at least {$rateSettings->min_rate}.",

            'rate.max' =>
            "The rate may not be greater than {$rateSettings->max_rate}.",

            'state.required' =>
            'The project state is required.',

            'state.in' =>
            'The selected project state is invalid.',

            'investments.required' =>
            'The investment type is required.',

            'investments.in' =>
            'The selected investment type is invalid.',

            'justification.required' =>
            'The justification is required.',

            'justification.in' =>
            'The selected justification is invalid.',

            'classification_of_investments.required' =>
            'The investment classification is required.',

            'classification_of_investments.in' =>
            'The selected investment classification is invalid.',

            'forecast_start_date.required' =>
            'The forecast start date is required.',

            'forecast_start_date.date' =>
            'The forecast start date must be a valid date.',

            'forecast_start_date.before_or_equal' =>
            'The start date must be before or equal to the finish date.',

            'forecast_end_date.required' =>
            'The forecast finish date is required.',

            'forecast_end_date.date' =>
            'The forecast finish date must be a valid date.',

            'forecast_end_date.after_or_equal' =>
            'The finish date must be after or equal to the start date.',

            'quartile_date.date' =>
            'The quartile date must be a valid date.',

            'approve_date.date' =>
            'The approve date must be a valid date.',

            'approve_date.after_or_equal' =>
            'The approve date must be after or equal to the start date.',

            'close_date.date' =>
            'The close date must be a valid date.',

            'close_date.after_or_equal' =>
            'The close date must be after or equal to the start date.',

            'data_uploaded.boolean' =>
            'The data uploaded status must be true or false.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'company_id' => 'company',
            'name' => 'project name',
            'order' => 'project order',
            'pda_code' => 'PDA code',
            'rate' => 'rate',
            'state' => 'project state',
            'investments' => 'investment type',
            'justification' => 'justification',
            'classification_of_investments' => 'classification of investments',
            'forecast_start_date' => 'forecast start date',
            'forecast_end_date' => 'forecast end date',
            'quartile_date' => 'quartile date',
            'approve_date' => 'approve date',
            'close_date' => 'close date',
            'data_uploaded' => 'data uploaded status',
        ];
    }
}
