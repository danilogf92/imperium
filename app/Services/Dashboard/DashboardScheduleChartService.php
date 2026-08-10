<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Collection;

class DashboardScheduleChartService
{
    public function build(array $statistics): array
    {
        $hasProjects = (bool) $statistics['hasProjects'];

        return [
            'plannedVsActualExecutionChart' => $hasProjects
                ? $this->plannedVsActualExecutionChart(
                    collect($statistics['plannedProjectsByMonth']),
                    collect($statistics['actualProjectsByMonth']),
                    (int) $statistics['projectCount']
                )
                : null,

            /*
             * Forecast End Date VS Close Date.
             *
             * Las dos series representan cantidad de proyectos
             * y se muestran como porcentaje acumulado.
             */
            'forecastVsCloseDateChart' => $hasProjects
                ? $this->forecastVsCloseDateChart(
                    collect($statistics['forecastEndDatesByMonth']),
                    collect($statistics['closeDatesByMonth']),
                    (int) $statistics['projectCount']
                )
                : null,

            /*
             * Totales utilizados por los footers.
             */
            'scheduleProjectTotal' => (int) $statistics['projectCount'],
        ];
    }

    private function plannedVsActualExecutionChart(
        Collection $plannedValues,
        Collection $actualValues,
        int $totalProjects
    ): array {
        $planned = 0;
        $actual = 0;
        $chartConfig = config('dashboard_charts.forecast_start_vs_approved');
        $plannedPercentages = [];
        $actualPercentages = [];
        $monthlyProjects = [];

        foreach (config('dashboard_charts.months') as $number => $month) {
            $monthlyCount = (int) $plannedValues->get($number, 0);
            $actualMonthlyCount = (int) $actualValues->get($number, 0);
            $planned += $monthlyCount;
            $actual += $actualMonthlyCount;
            $plannedPercentages[] = $totalProjects > 0
                ? round(($planned / $totalProjects) * 100, 2)
                : 0;
            $actualPercentages[] = $totalProjects > 0
                ? round(($actual / $totalProjects) * 100, 2)
                : 0;
            $monthlyProjects[] = $monthlyCount;
        }

        return $this->mixedChart(
            $plannedPercentages,
            $actualPercentages,
            $monthlyProjects,
            $chartConfig['planned_series_label'],
            $chartConfig['actual_series_label'],
            'Projects by forecast start date',
            $chartConfig
        );
    }

    private function forecastVsCloseDateChart(
        Collection $forecastValues,
        Collection $closeValues,
        int $totalProjects
    ): array {
        $forecast = 0;
        $closed = 0;
        $chartConfig = config('dashboard_charts.forecast_end_vs_close');
        $forecastPercentages = [];
        $closedPercentages = [];
        $monthlyProjects = [];

        foreach (config('dashboard_charts.months') as $number => $month) {
            $monthlyCount = (int) $forecastValues->get($number, 0);
            $closedMonthlyCount = (int) $closeValues->get($number, 0);
            $forecast += $monthlyCount;
            $closed += $closedMonthlyCount;
            $forecastPercentages[] = $totalProjects > 0
                ? round(($forecast / $totalProjects) * 100, 2)
                : 0;
            $closedPercentages[] = $totalProjects > 0
                ? round(($closed / $totalProjects) * 100, 2)
                : 0;
            $monthlyProjects[] = $monthlyCount;
        }

        return $this->mixedChart(
            $forecastPercentages,
            $closedPercentages,
            $monthlyProjects,
            $chartConfig['forecast_series_label'],
            $chartConfig['close_series_label'],
            'Projects by forecast end date',
            $chartConfig
        );
    }

    private function mixedChart(
        array $firstPercentages,
        array $secondPercentages,
        array $monthlyProjects,
        string $firstLabel,
        string $secondLabel,
        string $projectsLabel,
        array $chartConfig
    ): array {
        $projectsMaximum = max(1, ...$monthlyProjects);

        return [
            'series' => [
                ['name' => $firstLabel, 'type' => 'line', 'data' => $firstPercentages],
                ['name' => $secondLabel, 'type' => 'line', 'data' => $secondPercentages],
                ['name' => $projectsLabel, 'type' => 'column', 'data' => $monthlyProjects],
            ],
            'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#2563EB', '#16A34A', '#7C3AED'],
            'stroke' => ['width' => [3, 3, 0], 'curve' => 'straight'],
            'markers' => ['size' => [4, 4, 0]],
            'plotOptions' => ['bar' => ['columnWidth' => '52%', 'borderRadius' => 4]],
            'dataLabels' => ['enabled' => true, 'enabledOnSeries' => [2]],
            'xaxis' => ['categories' => array_values(config('dashboard_charts.months'))],
            'yaxis' => [
                [
                    'seriesName' => $firstLabel,
                    'min' => $chartConfig['y_axis_min'],
                    'max' => $chartConfig['y_axis_max'],
                    'tickAmount' => 5,
                    'title' => ['text' => 'Cumulative progress (%)'],
                    'labels' => ['formatter' => "function(value) { return Math.round(value) + '%'; }"],
                ],
                [
                    'seriesName' => $secondLabel,
                    'show' => false,
                    'min' => $chartConfig['y_axis_min'],
                    'max' => $chartConfig['y_axis_max'],
                    'tickAmount' => 5,
                ],
                [
                    'seriesName' => $projectsLabel,
                    'opposite' => true,
                    'min' => 0,
                    'max' => $projectsMaximum,
                    'tickAmount' => min(5, $projectsMaximum),
                    'decimalsInFloat' => 0,
                    'title' => ['text' => 'Projects'],
                    'labels' => ['formatter' => 'function(value) { return Math.round(value); }'],
                ],
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'y' => [
                    ['formatter' => "function(value) { return Number(value).toFixed(1) + '%'; }"],
                    ['formatter' => "function(value) { return Number(value).toFixed(1) + '%'; }"],
                    ['formatter' => "function(value) { return Math.round(value) + ' projects'; }"],
                ],
            ],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }
}
