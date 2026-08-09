<?php

namespace App\Services\Dashboard;

use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use App\Support\Dashboard\DashboardFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardQueryService
{
    public function projectQuery(User $user, DashboardFilters $filters): Builder
    {
        return $this->applyFilters(Project::query(), $user, $filters, 'projects');
    }

    public function dataQuery(User $user, DashboardFilters $filters): Builder
    {
        return $this->applyFilters(
            Data::query()->join('projects', 'projects.id', '=', 'data.project_id'),
            $user,
            $filters,
            'projects'
        );
    }

    public function availableYears(User $user): array
    {
        $query = $this->projectQuery($user, new DashboardFilters())
            ->whereNotNull('projects.forecast_start_date');

        return DB::connection()->getDriverName() === 'sqlite'
            ? $query
                ->selectRaw("strftime('%Y', projects.forecast_start_date) AS year")
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year): string => (string) $year)
                ->all()
            : $query
                ->selectRaw('YEAR(projects.forecast_start_date) AS year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year): string => (string) $year)
                ->all();
    }

    public function groupDataByProjectColumn(
        User $user,
        DashboardFilters $filters,
        string $groupColumn,
        string $valueColumn
    ): array {
        return $this->dataQuery($user, $filters)
            ->selectRaw(
                "{$groupColumn} AS label, COALESCE(SUM(data.{$valueColumn}), 0) AS total"
            )
            ->groupBy($groupColumn)
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    public function dataByMonth(
        User $user,
        DashboardFilters $filters,
        string $dateColumn,
        string $valueColumn,
        array $states = [],
        ?string $fallbackDateColumn = null
    ): array {
        $groupDate = $fallbackDateColumn
            ? "COALESCE({$dateColumn}, {$fallbackDateColumn})"
            : $dateColumn;

        return $this->dataQuery($user, $filters)
            ->when(
                $fallbackDateColumn === null,
                fn (Builder $query) => $query->whereNotNull($dateColumn)
            )
            ->when(
                $states !== [],
                fn (Builder $query) => $query->whereIn('projects.state', $states)
            )
            ->selectRaw(
                $this->monthNumberExpression($groupDate).
                " AS month, COALESCE(SUM(data.{$valueColumn}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    public function projectCountByMonth(
        User $user,
        DashboardFilters $filters,
        string $dateColumn,
        array $states = [],
        ?string $fallbackDateColumn = null
    ): array {
        $groupDate = $fallbackDateColumn
            ? "COALESCE({$dateColumn}, {$fallbackDateColumn})"
            : $dateColumn;

        return $this->projectQuery($user, $filters)
            ->when(
                $fallbackDateColumn === null,
                fn (Builder $query) => $query->whereNotNull($dateColumn)
            )
            ->when(
                $states !== [],
                fn (Builder $query) => $query->whereIn('projects.state', $states)
            )
            ->selectRaw(
                $this->monthNumberExpression($groupDate).' AS month, COUNT(*) AS total'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    public function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function monthNumberExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    private function applyFilters(
        Builder $query,
        User $user,
        DashboardFilters $filters,
        string $table
    ): Builder {
        $query->whereIn(
            "{$table}.company_id",
            $user->availableCompaniesQuery()
                ->select('companies.id')
                ->reorder()
        );

        if ($filters->companies !== []) {
            $query->whereExists(function ($companyQuery) use ($table, $filters): void {
                $companyQuery
                    ->selectRaw('1')
                    ->from('companies')
                    ->whereColumn('companies.id', "{$table}.company_id")
                    ->whereIn('companies.company_code', $filters->companies);
            });
        }

        if ($filters->years !== []) {
            $query->where(function (Builder $yearQuery) use ($table, $filters): void {
                foreach ($filters->years as $year) {
                    $yearQuery->orWhereBetween("{$table}.forecast_start_date", [
                        "{$year}-01-01 00:00:00",
                        "{$year}-12-31 23:59:59",
                    ]);
                }
            });
        }

        return $query
            ->when(
                $filters->states !== [],
                fn (Builder $q) => $q->whereIn("{$table}.state", $filters->states)
            )
            ->when(
                $filters->classifications !== [],
                fn (Builder $q) => $q->whereIn(
                    "{$table}.classification_of_investments",
                    $filters->classifications
                )
            )
            ->when(
                $filters->investments !== [],
                fn (Builder $q) => $q->whereIn(
                    "{$table}.investments",
                    $filters->investments
                )
            )
            ->when(
                $filters->justifications !== [],
                fn (Builder $q) => $q->whereIn(
                    "{$table}.justification",
                    $filters->justifications
                )
            );
    }
}
