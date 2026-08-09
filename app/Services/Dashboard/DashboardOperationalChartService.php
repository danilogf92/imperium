<?php

namespace App\Services\Dashboard;

use App\Support\Dashboard\DashboardCurrency;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

final class DashboardOperationalChartService
{
    private const COLORS = [
        '#2563eb', '#0ea5e9', '#14b8a6', '#22c55e', '#84cc16',
        '#eab308', '#f59e0b', '#f97316', '#ef4444', '#8b5cf6',
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
        $symbol = json_encode(DashboardCurrency::symbol($currency), JSON_THROW_ON_ERROR);

        return "function(value) { return {$symbol} + ' ' + Number(value).toLocaleString(undefined, {maximumFractionDigits: 2}); }";
    }
}
