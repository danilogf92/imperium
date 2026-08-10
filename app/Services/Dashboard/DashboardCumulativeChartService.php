<?php

namespace App\Services\Dashboard;

use App\Support\Dashboard\DashboardCurrency;

class DashboardCumulativeChartService
{
    public function build(array $statistics, string $currency): array
    {
        return [
            'cumulativeProjectsBudgetData' => $this->dataset(
                $statistics['projectsByForecastStartDateCreationMonth'],
                $statistics['budgetByCreationMonth'],
                (int) $statistics['projectCount'],
                $currency
            ),
            'cumulativeApprovedProjectsBudgetData' => $this->dataset(
                $statistics['projectsByApprovalMonth'],
                $statistics['approvedBudgetByMonth'],
                (int) $statistics['projectCount'],
                $currency
            ),
        ];
    }

    private function dataset(
        array $projects,
        array $budget,
        int $totalProjects,
        string $currency
    ): array {
        $projects = collect($projects);
        $budget = collect($budget);
        $hasData = $projects->isNotEmpty() || $budget->isNotEmpty();
        $totalProjects = max($totalProjects, 1);

        $cumulativeProjects = 0;
        $cumulativeBudget = 0.0;
        $projectPercentages = [];
        $budgetValues = [];

        $months = config('dashboard_charts.months');

        foreach ($months as $number => $month) {
            $cumulativeProjects += (int) $projects->get($number, 0);
            $cumulativeBudget += (float) $budget->get($number, 0);
            $projectPercentages[] = round(($cumulativeProjects / $totalProjects) * 100, 2);
            $budgetValues[] = round($cumulativeBudget, 2);
        }

        return [
            'categories' => array_values($months),
            'projectPercentages' => $projectPercentages,
            'budget' => $budgetValues,
            'currencySymbol' => DashboardCurrency::symbol($currency),
            'hasData' => $hasData,
        ];
    }
}
