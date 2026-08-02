<?php

namespace App\Enums;

enum InvestmentEnum: string
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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
