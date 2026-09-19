<?php

namespace App\Services\Resume;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CashFlowForecast
{
    public function rows(Collection $planned, Collection $actual, Collection $visiblePeriods): array
    {
        if ($visiblePeriods->isEmpty()) {
            return [];
        }
        $first = $planned->keys()->merge($actual->keys())->merge($visiblePeriods->keys())->sort()->first();
        $last = $visiblePeriods->keys()->sort()->last();
        $month = CarbonImmutable::createFromFormat('!Y-m', $first);
        $current = CarbonImmutable::now()->format('Y-m');
        $carry = 0.0;
        $result = [];
        while ($month->format('Y-m') <= $last) {
            $period = $month->format('Y-m');
            $base = (float) $planned->get($period, 0);
            $spent = (float) $actual->get($period, 0);
            $adjusted = round(max(0, $base + $carry), 2);
            $closed = $period < $current;
            $difference = round($spent - $base, 2);
            $outgoing = round($base + $carry - $spent, 2);
            if ($visiblePeriods->has($period)) {
                $result[] = [
                    'period' => $period, 'month' => $month->format('M Y'),
                    'status' => $closed ? 'Closed month' : ($period === $current ? 'In progress' : 'Forecast'),
                    'base' => $base, 'actual' => $spent, 'incoming' => $carry,
                    'adjusted' => $adjusted, 'remaining' => round(max(0, $adjusted - $spent), 2),
                    'difference' => $period <= $current ? $difference : null,
                    'variance_percent' => $period <= $current && $base != 0 ? round($difference / abs($base) * 100, 1) : null,
                    'outgoing' => $closed ? $outgoing : null,
                ];
            }
            // Future months assume the adjusted forecast is spent, avoiding repeated rollover.
            $carry = $closed ? $outgoing : min(0, $outgoing);
            $month = $month->addMonth();
        }

        return $result;
    }
}
