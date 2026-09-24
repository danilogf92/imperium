<?php

namespace App\Services\Resume;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CashFlowForecast
{
    /** Distribute each year's closed-month shortfall evenly over its remaining months. */
    public function annualProjection(Collection $planned, Collection $actual, Collection $visiblePeriods): array
    {
        $currentMonth = CarbonImmutable::now()->month;
        $rows = [];
        $summaries = [];

        foreach ($visiblePeriods->keys()->sort()->groupBy(fn (string $period) => substr($period, 0, 4)) as $year => $periods) {
            $cutoff = sprintf('%04d-%02d', $year, $currentMonth);
            $closed = $periods->filter(fn (string $period) => $period < $cutoff);
            $remaining = $periods->count() - $closed->count();
            $closedPlanned = round((float) $closed->sum(fn (string $period) => $planned->get($period, 0)), 2);
            $closedActual = round((float) $closed->sum(fn (string $period) => $actual->get($period, 0)), 2);
            $difference = round($closedPlanned - $closedActual, 2);
            $perMonth = $remaining > 0 ? $difference / $remaining : 0.0;

            $summaries[] = [
                'year' => (int) $year,
                'closed_months' => $closed->count(),
                'closed_planned' => $closedPlanned,
                'closed_actual' => $closedActual,
                'difference' => $difference,
                'remaining_months' => $remaining,
                'per_month' => $perMonth,
            ];

            foreach ($periods as $period) {
                $base = (float) $planned->get($period, 0);
                $rows[] = [
                    'period' => $period,
                    'month' => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'),
                    'planned' => $base,
                    'actual' => (float) $actual->get($period, 0),
                    'projected' => $period >= $cutoff ? round($base + $perMonth, 2) : null,
                ];
            }
        }

        return ['rows' => $rows, 'summaries' => $summaries];
    }

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
                    'period' => $period, 'month' => $month->translatedFormat('M Y'),
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
