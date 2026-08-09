<?php

namespace App\Services\Dashboard;

use App\Support\ChartValueFormatter;
use App\Support\Dashboard\DashboardCurrency;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\RadarChartModel;
use Illuminate\Support\Collection;

class DashboardPortfolioChartService
{
    private const INVESTMENT_COLORS = [
        'Innovation' => '#f59e0b',
        'Efficiency & Saving' => '#ef4444',
        'Replacement & Restructuring' => '#3b82f6',
        'Quality & Hygiene' => '#22c55e',
        'Health & Safety' => '#eab308',
        'Environment' => '#14b8a6',
        'Maintenance' => '#0ea5e9',
        'Capacity Increase' => '#8b5cf6',
    ];

    private const STATE_COLORS = [
        'Capex' => '#6366f1',
        'Planning' => '#f59e0b',
        'Execution' => '#0ea5e9',
        'Finished' => '#22c55e',
        'Postponed' => '#ef4444',
    ];

    public function build(array $statistics, string $currency): array
    {
        $projectsByInvestment = $this->objects($statistics['projectsByInvestment']);
        $projectsByState = $this->objects($statistics['projectsByState']);
        $budgetByInvestment = $this->objects($statistics['budgetByInvestment']);
        $budgetByState = $this->objects($statistics['budgetByState']);
        $budgetByArea = $this->objects($statistics['budgetByArea']);

        return [
            'projectsByInvestmentChart' =>
                $this->projectsByInvestmentChart($projectsByInvestment),
            'projectsByStateChart' =>
                $this->projectsByStateChart($projectsByState),
            'projectsByStateColumnChart' =>
                $this->projectsByStateColumnChart($projectsByState),
            'budgetByInvestmentChart' =>
                $this->budgetColumnChart($budgetByInvestment, $currency),
            'budgetByStateChart' =>
                $this->budgetStateChart($budgetByState, $currency),
            'budgetByStateColumnChart' =>
                $this->budgetStateColumnChart($budgetByState, $currency),
            'budgetByInvestmentRadarChart' =>
                $this->budgetInvestmentRadarChart($budgetByInvestment, $currency),
            'budgetByAreaRadarChart' =>
                $this->budgetAreaRadarChart($budgetByArea, $currency),
            'hasFinancialData' => $budgetByInvestment->isNotEmpty(),
            'hasAreaData' => $budgetByArea->isNotEmpty(),
        ];
    }

    private function objects(array $rows): Collection
    {
        return collect($rows)->map(fn (array $row) => (object) $row);
    }

    private function projectsByInvestmentChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)
            ->setTitle('Projects by investment')
            ->setAnimated(true)
            ->setHorizontal(true)
            ->setOpacity(1)
            ->disableShades()
            ->withDataLabels()
            ->withGrid();

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                (int) $value->total,
                self::INVESTMENT_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function projectsByStateChart(Collection $values): PieChartModel
    {
        $chart = (new PieChartModel)
            ->setTitle('Projects by state')
            ->setAnimated(true)
            ->setOpacity(1)
            ->disableShades()
            ->setType('donut')
            ->withDataLabels()
            ->withLegend()
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => $this->projectsFormatter(),
            ]);

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addSlice(
                $label,
                (int) $value->total,
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function projectsByStateColumnChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)
            ->setTitle('Project status #')
            ->setAnimated(true)
            ->withDataLabels()
            ->withGrid();

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                (int) $value->total,
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetColumnChart(
        Collection $values,
        string $currency
    ): ColumnChartModel {
        $chart = (new ColumnChartModel)
            ->setTitle('Budget by investment')
            ->setAnimated(true)
            ->setHorizontal(true)
            ->setOpacity(1)
            ->disableShades()
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig($this->moneyChartConfig('xaxis', $currency));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                round((float) $value->total, 2),
                self::INVESTMENT_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetStateChart(
        Collection $values,
        string $currency
    ): PieChartModel {
        $chart = (new PieChartModel)
            ->setTitle('Budget by state')
            ->setAnimated(true)
            ->setOpacity(1)
            ->disableShades()
            ->setType('donut')
            ->withDataLabels()
            ->withLegend()
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => $this->moneyFormatter($currency),
            ]);

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addSlice(
                $label,
                round((float) $value->total, 2),
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetStateColumnChart(
        Collection $values,
        string $currency
    ): ColumnChartModel {
        $chart = (new ColumnChartModel)
            ->setTitle('Project status value')
            ->setAnimated(true)
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig($this->moneyChartConfig('yaxis', $currency));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                round((float) $value->total, 2),
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetInvestmentRadarChart(
        Collection $values,
        string $currency
    ): RadarChartModel {
        $chart = (new RadarChartModel)
            ->setTitle('Type of Investment')
            ->setAnimated(true)
            ->setJsonConfig($this->moneyChartConfig('yaxis', $currency));

        foreach ($values as $value) {
            $chart->addSeries(
                'Investment',
                (string) $value->label,
                round((float) $value->total, 2)
            );
        }

        return $chart;
    }

    private function budgetAreaRadarChart(
        Collection $values,
        string $currency
    ): RadarChartModel {
        $chart = (new RadarChartModel)
            ->setTitle('Area Classification')
            ->setAnimated(true)
            ->setJsonConfig($this->moneyChartConfig('yaxis', $currency));

        foreach ($values as $value) {
            $chart->addSeries(
                'Investment',
                (string) $value->label,
                round((float) $value->total, 2)
            );
        }

        return $chart;
    }

    private function moneyChartConfig(string $axis, string $currency): array
    {
        $formatter = $this->moneyFormatter($currency);

        return [
            "{$axis}.labels.formatter" => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function moneyFormatter(string $currency): string
    {
        return ChartValueFormatter::compactMoney(DashboardCurrency::symbol($currency));
    }

    private function percentFormatter(): string
    {
        return "function(value) { return Number(value).toFixed(1) + '%'; }";
    }

    private function projectsFormatter(): string
    {
        return "function(value) { return Number(value).toLocaleString() + ' projects'; }";
    }
}
