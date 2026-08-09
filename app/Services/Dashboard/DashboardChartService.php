<?php

namespace App\Services\Dashboard;

class DashboardChartService
{
    public function __construct(
        private readonly DashboardPortfolioChartService $portfolioCharts,
        private readonly DashboardCumulativeChartService $cumulativeCharts,
        private readonly DashboardScheduleChartService $scheduleCharts,
        private readonly DashboardInsightChartService $insightCharts,
        private readonly DashboardOperationalChartService $operationalCharts
    ) {}

    public function build(array $statistics, string $currency): array
    {
        return [
            ...$this->portfolioCharts->build(
                $statistics,
                $currency
            ),

            ...$this->cumulativeCharts->build(
                $statistics,
                $currency
            ),

            ...$this->scheduleCharts->build(
                $statistics
            ),

            ...$this->insightCharts->build($statistics, $currency),

            ...$this->operationalCharts->build($statistics, $currency),
        ];
    }
}
