<?php

namespace App\Enums;

enum ProjectJustificationEnum: string implements \Filament\Support\Contracts\HasLabel
{
    case NormalCapex = 'Normal Capex';
    case SpecialProject = 'Special Project';
    case Sustainability = 'Sustainability';

    public function getLabel(): string
    {
        return __($this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
