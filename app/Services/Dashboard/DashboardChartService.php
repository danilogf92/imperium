<?php

namespace App\Services\Dashboard;

class DashboardChartService
{
    public function __construct(
        private readonly DashboardPortfolioChartService $portfolioCharts,
        private readonly DashboardCumulativeChartService $cumulativeCharts,
        private readonly DashboardScheduleChartService $scheduleCharts
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
        ];
    }
}
