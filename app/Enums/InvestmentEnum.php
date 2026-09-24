<?php

namespace App\Enums;

enum InvestmentEnum: string implements \Filament\Support\Contracts\HasLabel
{
    case Innovation = 'Innovation';
    case EfficiencySaving = 'Efficiency & Saving';
    case ReplacementRestructuring = 'Replacement & Restructuring';
    case QualityHygiene = 'Quality & Hygiene';
    case HealthSafety = 'Health & Safety';
    case Environment = 'Environment';
    case Maintenance = 'Maintenance';
    case CapacityIncrease = 'Capacity Increase';
    case Regulatory = 'Regulatory';

    public function getLabel(): string
    {
        return __($this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
