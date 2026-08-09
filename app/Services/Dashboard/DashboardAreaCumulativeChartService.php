<?php

namespace App\Services\Dashboard;

use App\Support\Dashboard\DashboardCurrency;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardCumulativeChartService
{
    public function build(array $statistics, string $currency): array
    {
        $projects = $this->toCollection(
            $statistics['projectsByCreationMonth']
        );

        $budget = $this->toCollection(
            $statistics['budgetByCreationMonth']
        );

        return [
            'cumulativeProjectsBudgetData' => $this->cumulativeProjectsBudgetData(
                $projects,
                $budget,
                $currency
            ),
        ];
    }

    private function cumulativeProjectsBudgetData(
        Collection $projects,
        Collection $budget,
        string $currency
    ): array {
        $months = $this->months(
            $projects,
            $budget
        );

        $projectsByMonth = $projects->keyBy('month');
        $budgetByMonth = $budget->keyBy('month');

        $cumulativeProjects = 0;
        $cumulativeBudget = 0.0;

        $categories = [];
        $projectValues = [];
        $budgetValues = [];

        foreach ($months as $month) {
            $cumulativeProjects +=
                (int) ($projectsByMonth->get($month)?->total ?? 0);

            $cumulativeBudget +=
                (float) ($budgetByMonth->get($month)?->total ?? 0);

            $categories[] = Carbon::createFromFormat(
                'Y-m',
                (string) $month
            )->translatedFormat('M Y');

            $projectValues[] = $cumulativeProjects;

            $budgetValues[] = round(
                $cumulativeBudget,
                2
            );
        }

        return [
            'categories' => $categories,

            'projects' => $projectValues,

            'budget' => $budgetValues,

            'currencySymbol' => DashboardCurrency::symbol(
                $currency
            ),

            'hasData' => $months->isNotEmpty(),
        ];
    }

    private function months(
        Collection $projects,
        Collection $budget
    ): Collection {
        return $projects
            ->pluck('month')
            ->merge(
                $budget->pluck('month')
            )
            ->unique()
            ->sort()
            ->values();
    }

    private function toCollection(array $rows): Collection
    {
        return collect($rows)
            ->map(
                fn (array $row) => (object) $row
            );
    }
}
