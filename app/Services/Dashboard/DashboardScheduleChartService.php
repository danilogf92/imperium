<?php

namespace App\Services\Dashboard;

use Asantibanez\LivewireCharts\Models\LineChartModel;
use Illuminate\Support\Collection;

class DashboardScheduleChartService
{
    private const MONTHS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];

    public function build(array $statistics): array
    {
        $hasProjects = (bool) $statistics['hasProjects'];

        return [
            /*
             * Forecast Start Date VS Approved Date.
             *
             * Las dos series utilizan Budgeted
             * y se muestran como porcentaje acumulado.
             */
            'plannedVsActualExecutionChart' => $hasProjects
                ? $this->plannedVsActualExecutionChart(
                    collect($statistics['budgetedByStartMonth']),
                    collect($statistics['budgetedByApprovalMonth']),
                    (float) $statistics['budgeted']
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
            'scheduleRealValueTotal' => (float) $statistics['budgeted'],
            'scheduleProjectTotal' => (int) $statistics['projectCount'],
        ];
    }

    private function plannedVsActualExecutionChart(
        Collection $plannedValues,
        Collection $actualValues,
        float $total
    ): LineChartModel {
        $chart = (new LineChartModel)
            ->setTitle('StartDate vs Approved Date')
            ->setAnimated(true)
            ->multiLine()
            ->setStraightCurve()
            ->setStrokeWidth(3)
            ->withGrid()
            ->withLegend()
            ->setColors([
                '#2563eb',
                '#16a34a',
            ])
            ->setJsonConfig(
                $this->percentageChartConfig()
            );

        $planned = 0.0;
        $actual = 0.0;

        foreach (self::MONTHS as $number => $month) {
            $planned +=
                (float) $plannedValues->get($number, 0);

            $actual +=
                (float) $actualValues->get($number, 0);

            $plannedPercentage = $total > 0
                ? round(($planned / $total) * 100, 2)
                : 0;

            $actualPercentage = $total > 0
                ? round(($actual / $total) * 100, 2)
                : 0;

            $chart
                ->addSeriesPoint(
                    'Planned % (Start date)',
                    $month,
                    $plannedPercentage
                )
                ->addSeriesPoint(
                    'Actual % (Approved date)',
                    $month,
                    $actualPercentage
                );
        }

        return $chart;
    }

    private function forecastVsCloseDateChart(
        Collection $forecastValues,
        Collection $closeValues,
        int $totalProjects
    ): LineChartModel {
        $chart = (new LineChartModel)
            ->setTitle('Forecast EndDate vs CloseDate')
            ->setAnimated(true)
            ->multiLine()
            ->setStraightCurve()
            ->setStrokeWidth(3)
            ->withGrid()
            ->withLegend()
            ->setColors([
                '#2563eb',
                '#16a34a',
            ])
            ->setJsonConfig(
                $this->percentageChartConfig()
            );

        $forecast = 0;
        $closed = 0;

        foreach (self::MONTHS as $number => $month) {
            $forecast +=
                (int) $forecastValues->get($number, 0);

            $closed +=
                (int) $closeValues->get($number, 0);

            $forecastPercentage = $totalProjects > 0
                ? round(($forecast / $totalProjects) * 100, 2)
                : 0;

            $closedPercentage = $totalProjects > 0
                ? round(($closed / $totalProjects) * 100, 2)
                : 0;

            $chart
                ->addSeriesPoint(
                    'Forecast End Date %',
                    $month,
                    $forecastPercentage
                )
                ->addSeriesPoint(
                    'Close Date %',
                    $month,
                    $closedPercentage
                );
        }

        return $chart;
    }

    private function percentageChartConfig(): array
    {
        return [
            'yaxis.min' => 0,
            'yaxis.max' => 100,
            'yaxis.tickAmount' => 5,

            'yaxis.labels.formatter' => '(value) => `${Math.round(value)}%`',

            'tooltip.y.formatter' => '(value) => `${Number(value).toFixed(1)}%`',

            'markers.size' => 4,
        ];
    }
}
