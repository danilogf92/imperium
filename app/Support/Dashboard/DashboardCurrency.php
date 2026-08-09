<?php

namespace App\Support\Dashboard;

final class DashboardCurrency
{
    public static function columns(string $currency): array
    {
        return $currency === 'dollar'
            ? [
                'budgeted' => 'global_price',
                'booked' => 'booked',
                'executed' => 'executed_dollars',
                'real_value' => 'real_value',
            ]
            : [
                'budgeted' => 'global_price_euros',
                'booked' => 'booked_euros',
                'executed' => 'executed_euros',
                'real_value' => 'real_value_euros',
            ];
    }

    public static function symbol(string $currency): string
    {
        return $currency === 'dollar' ? '$' : '€';
    }
}
