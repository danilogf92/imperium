<?php

namespace App\Validation;

use App\Models\Project;

class ProjectUpdateValidation
{
    public static function rules(Project $project): array
    {
        $rules = ProjectCreateValidation::rules();
        $rules['pda_code'] = ProjectCreateValidation::pdaRules(
            (int) $project->getKey()
        );

        return $rules;
    }

    public static function messages(): array
    {
        return ProjectCreateValidation::messages();
    }

    public static function attributes(): array
    {
        return ProjectCreateValidation::attributes();
    }
}
