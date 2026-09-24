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

            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'distinct', 'exists:owners,id'],

            'sap_order' => ['nullable', 'string', 'max:255'],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Order ya no es obligatorio.
             *
             * Si viene informado:
             * - debe ser texto
             * - debe comenzar con número
             * - puede terminar con letras
             *
             * Ejemplos válidos:
             * 10
             * 10a
             * 10b
             */
            'order' => [
                'nullable',
                'string',
                'regex:/^\d+[a-z]*$/i',
                'max:20',
            ],

            'pda_code' => self::pdaRules(),

            'rate' => [
                'required',
                'numeric',
                'min:'.$rateSettings->min_rate,
                'max:'.$rateSettings->max_rate,
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

            /*
             * Forecast Start Date
             *
             * Es obligatoria excepto cuando:
             * state = Postponed
             */
            'forecast_start_date' => [
                'nullable',
                'required_unless:state,'.ProjectStateEnum::Postponed->value,
                'date',
                'before_or_equal:forecast_end_date',
            ],

            /*
             * Forecast End Date
             *
             * Es obligatoria excepto cuando:
             * state = Postponed
             */
            'forecast_end_date' => [
                'nullable',
                'required_unless:state,'.ProjectStateEnum::Postponed->value,
                'date',
                'after_or_equal:forecast_start_date',
            ],

            /*
             * Quartile Date
             *
             * Siempre opcional.
             */
            'quartile_date' => [
                'nullable',
                'date',
            ],

            /*
             * Approve Date
             *
             * Siempre opcional.
             *
             * Si existe forecast_start_date,
             * debe ser igual o posterior.
             */
            'approve_date' => [
                'nullable',
                'date',
                'after_or_equal:forecast_start_date',
            ],

            /*
             * Close Date
             *
             * Siempre opcional.
             *
             * Debe ser:
             * - igual o posterior a forecast_start_date
             * - igual o posterior a approve_date cuando approve_date exista
             */
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
        $uniquePdaCode = Rule::unique(
            'projects',
            'pda_code'
        );

        /*
         * Cuando editamos un proyecto,
         * ignoramos su propio ID para que
         * el PDA actual no falle por unique.
         */
        if ($ignoreProjectId) {
            $uniquePdaCode->ignore(
                $ignoreProjectId
            );
        }

        return [
            'required',
            'string',
            'regex:/^.+-.+$/',
            'max:255',
            $uniquePdaCode,
        ];
    }

    public static function messages(): array
    {
        $rateSettings = ProjectRateSetting::current();

        return [
            /*
             * Company
             */
            'company_id.required' => __('The company is required.'),

            'company_id.integer' => __('The selected company is invalid.'),

            'company_id.exists' => __('The selected company does not exist.'),

            /*
             * Name
             */
            'name.required' => __('The project name is required.'),

            'name.string' => __('The project name must be valid text.'),

            'name.max' => __('The project name may not be greater than 255 characters.'),

            /*
             * Order
             *
             * Ya no existe mensaje required.
             */
            'order.string' => __('The project order must be valid text.'),

            'order.regex' => __('The project order must start with a number and may end with letters, for example 10, 10a, or 10b.'),

            'order.max' => __('The project order may not be greater than 20 characters.'),

            'order.unique' => __('This project order is already in use for the selected plant.'),

            /*
             * PDA
             */
            'pda_code.required' => __('The PDA code is required.'),

            'pda_code.string' => __('The PDA code must be valid text.'),

            'pda_code.regex' => __('The PDA code is required.'),

            'pda_code.max' => __('The PDA code may not be greater than 255 characters.'),

            'pda_code.unique' => __('The PDA code is already registered.'),

            /*
             * Rate
             */
            'rate.required' => __('The rate is required.'),

            'rate.numeric' => __('The rate must be numeric.'),

            'rate.min' => "The rate must be at least {$rateSettings->min_rate}.",

            'rate.max' => "The rate may not be greater than {$rateSettings->max_rate}.",

            /*
             * State
             */
            'state.required' => __('The project state is required.'),

            'state.in' => __('The selected project state is invalid.'),

            /*
             * Investments
             */
            'investments.required' => __('The investment type is required.'),

            'investments.in' => __('The selected investment type is invalid.'),

            /*
             * Justification
             */
            'justification.required' => __('The justification is required.'),

            'justification.in' => __('The selected justification is invalid.'),

            /*
             * Classification
             */
            'classification_of_investments.required' => __('The investment classification is required.'),

            'classification_of_investments.in' => __('The selected investment classification is invalid.'),

            /*
             * Forecast Start Date
             */
            'forecast_start_date.required_unless' => __('The forecast start date is required unless the project is postponed.'),

            'forecast_start_date.date' => __('The forecast start date must be a valid date.'),

            'forecast_start_date.before_or_equal' => __('The start date must be before or equal to the finish date.'),

            /*
             * Forecast End Date
             */
            'forecast_end_date.required_unless' => __('The forecast finish date is required unless the project is postponed.'),

            'forecast_end_date.date' => __('The forecast finish date must be a valid date.'),

            'forecast_end_date.after_or_equal' => __('The finish date must be after or equal to the start date.'),

            /*
             * Quartile Date
             */
            'quartile_date.date' => __('The quartile date must be a valid date.'),

            /*
             * Approve Date
             */
            'approve_date.date' => __('The approve date must be a valid date.'),

            'approve_date.after_or_equal' => __('The approve date must be after or equal to the start date.'),

            /*
             * Close Date
             */
            'close_date.date' => __('The close date must be a valid date.'),

            'close_date.after_or_equal' => __('The close date must be after or equal to the start date.'),

            /*
             * Data Uploaded
             */
            'data_uploaded.boolean' => __('The data uploaded status must be true or false.'),
        ];
    }

    public static function attributes(): array
    {
        return [
            'company_id' => 'company',

            'owner_ids' => 'owners',
            'owner_ids.*' => 'owner',

            'sap_order' => 'SAP order',

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
