<?php

namespace App\Services\Dashboard;

use App\Support\ChartValueFormatter;
use App\Support\Dashboard\DashboardCurrency;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;

final class DashboardInsightChartService
{
    public function build(array $statistics, string $currency): array
    {
        $projectCount = (int) $statistics['projectCount'];
        $projectsWithData = (int) $statistics['projectsWithData'];
        $budgeted = (float) $statistics['budgeted'];
        $booked = (float) $statistics['booked'];

        return [
            'financialFlowChart' => $this->financialFlowChart($statistics, $currency),
            'budgetAvailabilityChart' => $this->budgetAvailabilityChart($budgeted, $booked, $currency),
            'dataCoverageChart' => $this->dataCoverageChart($projectCount, $projectsWithData),
            'portfolioStageChart' => $this->portfolioStageChart($statistics['projectsByState']),
            'hasInsightFinancialData' => $budgeted > 0 || $booked > 0,
        ];
    }

    private function financialFlowChart(array $statistics, string $currency): ColumnChartModel
    {
        $chart = (new ColumnChartModel)
            ->setTitle('Financial flow')
            ->setAnimated(true)
            ->setOpacity(1)
            ->setColors(['#2563EB', '#D97706', '#DC2626', '#16A34A'])
            ->disableShades()
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig($this->moneyConfig($currency));

        foreach ([
            ['Budgeted', (float) $statistics['budgeted'], '#2563eb'],
            ['Booked', (float) $statistics['booked'], '#f59e0b'],
            ['Executed', (float) $statistics['executed'], '#ef4444'],
            ['Real value', (float) $statistics['realValue'], '#16a34a'],
        ] as [$label, $value, $color]) {
            $chart->addColumn($label, round($value, 2), $color);
        }

        return $chart;
    }

    private function budgetAvailabilityChart(float $budgeted, float $booked, string $currency): PieChartModel
    {
        $committed = min(max($booked, 0), max($budgeted, 0));
        $available = max($budgeted - $booked, 0);

        return (new PieChartModel)
            ->setTitle('Committed vs available budget')
            ->setAnimated(true)
            ->setType('donut')
            ->setColors(['#D97706', '#16A34A'])
            ->disableShades()
            ->withDataLabels()
            ->withLegend()
            ->addSlice('Committed', round($committed, 2), '#f59e0b')
            ->addSlice('Available', round($available, 2), '#22c55e')
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => $this->moneyFormatter($currency),
            ]);
    }

    private function dataCoverageChart(int $projectCount, int $projectsWithData): PieChartModel
    {
        return (new PieChartModel)
            ->setTitle('Project data coverage')
            ->setAnimated(true)
            ->setType('donut')
            ->setColors(['#2563EB', '#94A3B8'])
            ->disableShades()
            ->withDataLabels()
            ->withLegend()
            ->addSlice('With financial data', $projectsWithData, '#2563eb')
            ->addSlice('Without financial data', max($projectCount - $projectsWithData, 0), '#cbd5e1')
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => "function(value) { return Number(value).toLocaleString() + ' projects'; }",
            ]);
    }

    private function portfolioStageChart(array $projectsByState): PieChartModel
    {
        $states = collect($projectsByState)->pluck('total', 'label');
        $stages = [
            'Pre-execution' => (int) $states->only(['Capex', 'Planning'])->sum(),
            'Execution' => (int) ($states['Execution'] ?? 0),
            'Finished' => (int) ($states['Finished'] ?? 0),
            'Postponed' => (int) ($states['Postponed'] ?? 0),
        ];
        $colors = ['#6366f1', '#0ea5e9', '#22c55e', '#ef4444'];
        $chart = (new PieChartModel)
            ->setTitle('Portfolio stage')
            ->setAnimated(true)
            ->setType('donut')
            ->setColors(['#7C3AED', '#0891B2', '#16A34A', '#DC2626'])
            ->disableShades()
            ->withDataLabels()
            ->withLegend()
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => "function(value) { return Number(value).toLocaleString() + ' projects'; }",
            ]);

        foreach ($stages as $index => $total) {
            $chart->addSlice($index, $total, $colors[array_search($index, array_keys($stages), true)]);
        }

        return $chart;
    }

    private function moneyConfig(string $currency): array
    {
        $formatter = $this->moneyFormatter($currency);

        return [
            'yaxis.labels.formatter' => $formatter,
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
}
