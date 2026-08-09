<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Support\Dashboard\DashboardCurrency;
use App\Support\Dashboard\DashboardFilters;

class DashboardStatisticsService
{
    public function __construct(
        private readonly DashboardQueryService $queries
    ) {}

    public function load(User $user, DashboardFilters $filters): array
    {
        $columns = DashboardCurrency::columns($filters->currency);

        $projectCount = $this->queries
            ->projectQuery($user, $filters)
            ->count();

        /*
         * Cantidad de proyectos por tipo de inversión.
         */
        $projectsByInvestment = $this->queries
            ->projectQuery($user, $filters)
            ->selectRaw('investments AS label, COUNT(*) AS total')
            ->groupBy('investments')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (int) $row->total,
            ])
            ->all();

        /*
         * Cantidad de proyectos por estado.
         */
        $projectsByState = $this->queries
            ->projectQuery($user, $filters)
            ->selectRaw('state AS label, COUNT(*) AS total')
            ->groupBy('state')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (int) $row->total,
            ])
            ->all();

        /*
         * Totales financieros.
         */
        $financialTotals = $this->queries
            ->dataQuery($user, $filters)
            ->selectRaw(
                'COUNT(DISTINCT data.project_id) AS projects_with_data, '.
                "COALESCE(SUM(data.{$columns['budgeted']}), 0) AS budgeted, ".
                "COALESCE(SUM(data.{$columns['booked']}), 0) AS booked, ".
                "COALESCE(SUM(data.{$columns['executed']}), 0) AS executed, ".
                "COALESCE(SUM(data.{$columns['real_value']}), 0) AS real_value"
            )
            ->first();

        /*
         * Budgeted por tipo de inversión.
         */
        $budgetByInvestment = $this->queries->groupDataByProjectColumn(
            $user,
            $filters,
            'projects.investments',
            $columns['budgeted']
        );

        /*
         * Budgeted por estado.
         *
         * Se guarda en una variable porque esta misma información
         * se utiliza también para calcular Execution + Finished.
         */
        $budgetByState = $this->queries->groupDataByProjectColumn(
            $user,
            $filters,
            'projects.state',
            $columns['budgeted']
        );

        /*
         * Budgeted acumulado de proyectos que están en:
         *
         * - Execution
         * - Finished
         *
         * Respeta todos los filtros activos y la moneda seleccionada.
         */
        $executionFinishedBudget = collect($budgetByState)
            ->whereIn('label', [
                'Execution',
                'Finished',
            ])
            ->sum('total');

        /*
         * Presupuesto por área.
         */
        $budgetByArea = $this->queries
            ->dataQuery($user, $filters)
            ->whereNotNull('data.area')
            ->where('data.area', '<>', '')
            ->selectRaw(
                'data.area AS label, '.
                "COALESCE(SUM(data.{$columns['budgeted']}), 0) AS total"
            )
            ->groupBy('data.area')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (float) $row->total,
            ])
            ->all();

        /*
         * Proyectos creados por mes.
         */
        $projectsByCreationMonth = $this->queries->projectCountByMonth(
            $user,
            $filters,
            'projects.created_at'
        );

        /*
         * Budgeted agrupado por mes de creación.
         */
        $budgetByCreationMonth = $this->queries->dataByMonth(
            $user,
            $filters,
            'projects.created_at',
            $columns['budgeted']
        );

        $projectsByApprovalMonth = $this->queries->projectCountByMonth(
            $user,
            $filters,
            'projects.approve_date',
            ['Execution', 'Finished'],
            'projects.updated_at'
        );

        $approvedBudgetByMonth = $this->queries->dataByMonth(
            $user,
            $filters,
            'projects.approve_date',
            $columns['budgeted'],
            ['Execution', 'Finished'],
            'projects.updated_at'
        );

        /*
         * IMPORTANTE:
         *
         * Forecast Start Date VS Budgeted
         *
         * USD:
         * data.global_price
         *
         * EUR:
         * data.global_price_euros
         */
        $budgetedByStartMonth = $this->queries->dataByMonth(
            $user,
            $filters,
            'projects.forecast_start_date',
            $columns['budgeted']
        );

        /*
         * IMPORTANTE:
         *
         * Approve Date VS Budgeted
         *
         * USD:
         * data.global_price
         *
         * EUR:
         * data.global_price_euros
         */
        $budgetedByApprovalMonth = $this->queries->dataByMonth(
            $user,
            $filters,
            'projects.approve_date',
            $columns['budgeted']
        );

        return [
            'projectCount' => $projectCount,

            'projectsWithData' => (int) ($financialTotals->projects_with_data ?? 0),

            'hasProjects' => $projectCount > 0,

            'budgeted' => (float) ($financialTotals->budgeted ?? 0),

            'booked' => (float) ($financialTotals->booked ?? 0),

            'executed' => (float) ($financialTotals->executed ?? 0),

            'realValue' => (float) ($financialTotals->real_value ?? 0),

            /*
             * Nueva métrica:
             * Budgeted de Execution + Finished.
             */
            'executionFinishedBudget' => (float) $executionFinishedBudget,

            'projectsByInvestment' => $projectsByInvestment,

            'projectsByState' => $projectsByState,

            'budgetByInvestment' => $budgetByInvestment,

            'budgetByState' => $budgetByState,

            'budgetByArea' => $budgetByArea,

            'projectsByCompany' => $this->queries->projectCountByCompany($user, $filters),

            'budgetByCompany' => $this->queries->dataByCompany(
                $user,
                $filters,
                $columns['budgeted']
            ),

            'bookedBySupplier' => $this->queries->dataBySupplier(
                $user,
                $filters,
                $columns['booked']
            ),

            'executedBySupplier' => $this->queries->dataBySupplier(
                $user,
                $filters,
                $columns['executed']
            ),

            'projectsByCreationMonth' => $projectsByCreationMonth,

            'budgetByCreationMonth' => $budgetByCreationMonth,

            'projectsByApprovalMonth' => $projectsByApprovalMonth,

            'approvedBudgetByMonth' => $approvedBudgetByMonth,

            /*
             * Nuevas series basadas en Budgeted.
             */
            'budgetedByStartMonth' => $budgetedByStartMonth,

            'budgetedByApprovalMonth' => $budgetedByApprovalMonth,

            'forecastEndDatesByMonth' => $this->queries->projectCountByMonth(
                $user,
                $filters,
                'projects.forecast_end_date'
            ),

            'closeDatesByMonth' => $this->queries->projectCountByMonth(
                $user,
                $filters,
                'projects.close_date'
            ),
        ];
    }

    public function availableYears(User $user): array
    {
        return $this->queries->availableYears($user);
    }
}
