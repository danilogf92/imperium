<?php

namespace App\Services\Dashboard;

use App\Support\ChartValueFormatter;
use App\Support\Dashboard\DashboardCurrency;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

final class DashboardOperationalChartService
{
    private const COLORS = [
        '#2563EB', '#DC2626', '#16A34A', '#D97706', '#7C3AED',
        '#DB2777', '#0891B2', '#4D7C0F', '#EA580C', '#475569',
    ];

    public function build(array $statistics, string $currency): array
    {
        return [
            'projectsByCompanyChart' => $this->chart(
                $statistics['projectsByCompany'],
                'Projects by plant',
                false,
                $currency
            ),
            'budgetByCompanyChart' => $this->chart(
                $statistics['budgetByCompany'],
                'Budget by plant',
                true,
                $currency
            ),
            'bookedBySupplierChart' => $this->chart(
                $statistics['bookedBySupplier'],
                'Booked value by supplier',
                true,
                $currency
            ),
            'executedBySupplierChart' => $this->chart(
                $statistics['executedBySupplier'],
                'Executed value by supplier',
                true,
                $currency
            ),
            'hasSupplierBookedData' => $statistics['bookedBySupplier'] !== [],
            'hasSupplierExecutedData' => $statistics['executedBySupplier'] !== [],
        ];
    }

    private function chart(array $rows, string $title, bool $money, string $currency): ColumnChartModel
    {
        $chart = (new ColumnChartModel)
            ->setTitle($title)
            ->setAnimated(true)
            ->setHorizontal(true)
            ->setOpacity(1)
            ->setColors(self::COLORS)
            ->disableShades()
            ->withDataLabels()
            ->withGrid();

        if ($money) {
            $formatter = $this->moneyFormatter($currency);
            $chart->setJsonConfig([
                'xaxis.labels.formatter' => $formatter,
                'dataLabels.formatter' => $formatter,
                'tooltip.y.formatter' => $formatter,
            ]);
        } else {
            $chart->setJsonConfig([
                'dataLabels.formatter' => "function(value) { return Number(value).toLocaleString(); }",
                'tooltip.y.formatter' => "function(value) { return Number(value).toLocaleString() + ' projects'; }",
            ]);
        }

        foreach (array_values($rows) as $index => $row) {
            $chart->addColumn(
                (string) $row['label'],
                $money ? round((float) $row['total'], 2) : (int) $row['total'],
                self::COLORS[$index % count(self::COLORS)]
            );
        }

        return $chart;
    }

    private function moneyFormatter(string $currency): string
    {
        return ChartValueFormatter::compactMoney(DashboardCurrency::symbol($currency));
    }
}
