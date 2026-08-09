<?php

namespace App\Services\Dashboard;

use App\Support\Dashboard\DashboardCurrency;
class DashboardCumulativeChartService
{
    private const MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    public function build(array $statistics, string $currency): array
    {
        return [
            'cumulativeProjectsBudgetData' => $this->dataset(
                $statistics['projectsByCreationMonth'],
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

        foreach (self::MONTHS as $number => $month) {
            $cumulativeProjects += (int) $projects->get($number, 0);
            $cumulativeBudget += (float) $budget->get($number, 0);
            $projectPercentages[] = round(($cumulativeProjects / $totalProjects) * 100, 2);
            $budgetValues[] = round($cumulativeBudget, 2);
        }

        return [
            'categories' => array_values(self::MONTHS),
            'projectPercentages' => $projectPercentages,
            'budget' => $budgetValues,
            'currencySymbol' => DashboardCurrency::symbol($currency),
            'hasData' => $hasData,
        ];
    }
}
