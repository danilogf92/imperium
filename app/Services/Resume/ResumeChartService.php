<?php

namespace App\Services\Resume;

use App\Support\ChartValueFormatter;
use Illuminate\Support\Collection;

class ResumeChartService
{
    /** @return array<string, array<string, mixed>> */
    public function additionalCharts(
        Collection $rows,
        string $symbol,
        ?Collection $coverageRows = null
    ): array
    {
        $years = $rows->pluck('year')->map(fn ($year): string => (string) $year)->values()->all();
        $coverageRows ??= $rows;
        $coverageYears = $coverageRows->pluck('year')
            ->map(fn ($year): string => (string) $year)->values()->all();
        $moneyFormatter = ChartValueFormatter::compactMoney($symbol);
        $coverageSeries = [
            ['name' => 'Approved / Budgeted', 'data' => $this->ratios($coverageRows, 'approved', 'budgeted')],
            ['name' => 'Booked / Approved', 'data' => $this->ratios($coverageRows, 'booked', 'approved')],
            ['name' => 'Booked / Budgeted', 'data' => $this->ratios($coverageRows, 'booked', 'budgeted')],
            ['name' => 'Executed / Budgeted', 'data' => $this->ratios($coverageRows, 'executed', 'budgeted')],
        ];
        $coverageMaximum = max(
            100,
            (int) (ceil(collect($coverageSeries)->pluck('data')->flatten()->max() / 10) * 10)
        );

        return [
            'coverageChartOptions' => [
                'series' => $coverageSeries,
                'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
                'colors' => ['#2563EB', '#F59E0B', '#059669', '#7C3AED'],
                'stroke' => ['curve' => 'smooth', 'width' => 3],
                'markers' => ['size' => 5],
                'dataLabels' => ['enabled' => false],
                'xaxis' => ['categories' => $coverageYears],
                'yaxis' => [
                    'labels' => ['formatter' => "function(value) { return Number(value).toFixed(1) + '%'; }"],
                    'title' => ['text' => 'Coverage (%)'],
                    'min' => 0,
                    'max' => $coverageMaximum,
                    'forceNiceScale' => false,
                ],
                'annotations' => [
                    'yaxis' => [[
                        'y' => 100,
                        'borderColor' => '#DC2626',
                        'strokeDashArray' => 6,
                        'label' => [
                            'text' => '100% target',
                            'borderColor' => '#DC2626',
                            'style' => ['background' => '#DC2626', 'color' => '#FFFFFF'],
                        ],
                    ]],
                ],
                'tooltip' => ['y' => ['formatter' => "function(value) { return Number(value).toFixed(2) + '%'; }"]],
                'legend' => ['show' => true, 'position' => 'top'],
                'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
            ],
            'averageChartOptions' => [
                'series' => [
                    ['name' => 'Average budgeted', 'type' => 'column', 'data' => $this->averages($rows, 'budgeted')],
                    ['name' => 'Average approved', 'type' => 'line', 'data' => $this->averages($rows, 'approved')],
                    ['name' => 'Average booked', 'type' => 'line', 'data' => $this->averages($rows, 'booked')],
                ],
                'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
                'colors' => ['#2563EB', '#7C3AED', '#EA580C'],
                'stroke' => ['width' => [0, 4, 4], 'curve' => 'smooth'],
                'markers' => ['size' => [0, 5, 5], 'strokeWidth' => 2, 'hover' => ['sizeOffset' => 2]],
                'plotOptions' => ['bar' => ['columnWidth' => '46%', 'borderRadius' => 5]],
                'dataLabels' => ['enabled' => false],
                'xaxis' => ['categories' => $years],
                'yaxis' => [
                    'labels' => ['formatter' => $moneyFormatter, 'minWidth' => 80, 'maxWidth' => 130],
                    'title' => ['text' => "Average value ({$symbol})"],
                ],
                'tooltip' => ['y' => ['formatter' => $moneyFormatter]],
                'legend' => ['show' => true, 'position' => 'top'],
                'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
            ],
        ];
    }

    private function ratios(Collection $rows, string $numerator, string $denominator): array
    {
        return $rows->map(fn (array $row): float => $row[$denominator] == 0
            ? 0
            : round($row[$numerator] / $row[$denominator] * 100, 2))->values()->all();
    }

    private function averages(Collection $rows, string $field): array
    {
        return $rows->map(fn (array $row): float => $row['project_count'] === 0
            ? 0
            : round($row[$field] / $row['project_count'], 2))->values()->all();
    }

}
