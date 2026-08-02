<?php

namespace App\Enums;

enum ProjectJustificationEnum: string
{
    case NormalCapex = 'Normal Capex';
    case SpecialProject = 'Special Project';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
