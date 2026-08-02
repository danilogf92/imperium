<?php

namespace App\Enums;

enum ProjectStateEnum: string
{
    case Capex = 'Capex';
    case Planning = 'Planning';
    case Execution = 'Execution';
    case Finished = 'Finished';
    case Postponed = 'Postponed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function color(): string
    {
        return match ($this) {
            self::Capex => '#7C3AED',
            self::Planning => '#F59E0B',
            self::Execution => '#2563EB',
            self::Finished => '#059669',
            self::Postponed => '#ff0900',
        };
    }

    public function softColor(): string
    {
        return match ($this) {
            self::Capex => '#EDE9FE',
            self::Planning => '#FEF3C7',
            self::Execution => '#DBEAFE',
            self::Finished => '#D1FAE5',
            self::Postponed => '#F3F4F6',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Capex => '#5B21B6',
            self::Planning => '#92400E',
            self::Execution => '#1E40AF',
            self::Finished => '#065F46',
            self::Postponed => '#374151',
        };
    }
}
