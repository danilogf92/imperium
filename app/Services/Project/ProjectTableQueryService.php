<?php

namespace App\Services\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class ProjectTableQueryService
{
    public function filtered(array $filters): Builder
    {
        $user = auth()->user();
        abort_unless($user, 403);

        return Project::query()
            ->with([
                'company:id,company_code,company_name',
                'creator:id,name',
                'responsible:id,name',
            ])

            ->withExists([
                'data as has_orders' => fn (Builder $query): Builder => $query
                    ->whereNotNull('order_no')
                    ->where('order_no', '<>', ''),
            ])

            /*
             * Financial information - EUR
             */
            ->withSum('data as budgeted_euros', 'global_price_euros')
            ->withSum('data as real_euros', 'real_value_euros')
            ->withSum('data as executed_euros', 'executed_euros')
            ->withSum('data as booked_euros', 'booked_euros')

            /*
             * Financial information - USD
             */
            ->withSum('data as budgeted_dollars', 'global_price')
            ->withSum('data as real_dollars', 'real_value')
            ->withSum('data as executed_dollars', 'executed_dollars')
            ->withSum('data as booked', 'booked')

            /*
             * Permissions
             */
            ->whereIn(
                'company_id',
                $user
                    ->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            )

            /*
             * Search
             */
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $term = '%'.$filters['search'].'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', $term)
                        ->orWhere('order', 'like', $term)
                        ->orWhere('pda_code', 'like', $term)
                        ->orWhere('state', 'like', $term)
                        ->orWhere('classification_of_investments', 'like', $term)
                        ->orWhere('investments', 'like', $term)
                        ->orWhere('justification', 'like', $term);
                });
            })

            /*
             * Years
             */
            ->when($filters['years'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    foreach ($filters['years'] as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            })

            /*
             * States
             */
            ->when(
                $filters['states'] !== [],
                fn (Builder $query) => $query->whereIn('state', $filters['states'])
            )

            /*
             * Project type
             */
            ->when(
                $filters['types'] !== [],
                fn (Builder $query) => $query->whereIn(
                    'classification_of_investments',
                    $filters['types']
                )
            )

            /*
             * Investments
             */
            ->when(
                $filters['investments'] !== [],
                fn (Builder $query) => $query->whereIn('investments', $filters['investments'])
            )

            /*
             * Plants
             */
            ->when(
                $filters['plants'] !== [],
                fn (Builder $query) => $query->whereHas(
                    'company',
                    fn (Builder $company) => $company->whereIn(
                        'company_code',
                        $filters['plants']
                    )
                )
            )

            /*
             * Project ideas
             */
            ->when(
                count($filters['projectIdeas']) === 1,
                fn (Builder $query) => $filters['projectIdeas'][0] === 'with'
                        ? $query->whereNotNull('project_idea_path')
                        : $query->whereNull('project_idea_path')
            );
    }

    public function applyOrder(
        Builder $query,
        bool $byRest,
        string $column,
        string $direction
    ): void {
        if ($byRest) {
            $query
                ->where('state', '!=', 'Finished')
                ->where('data_uploaded', true)
                ->whereHas('data')
                ->addSelect([
                    'rest' => Data::query()
                        ->selectRaw(
                            'COALESCE(SUM(global_price_euros), 0) - COALESCE(SUM(booked_euros), 0)'
                        )
                        ->whereColumn('project_id', 'projects.id'),
                ])
                ->orderByDesc('rest');

            return;
        }

        if ($column !== 'order') {
            $query->orderBy($column, $direction);

            return;
        }

        $direction = strtoupper($direction) === 'ASC'
            ? 'ASC'
            : 'DESC';

        $mysql = DB::connection()->getDriverName() === 'mysql';

        $quoted = $mysql
            ? '`projects`.`order`'
            : 'projects."order"';

        $integer = $mysql
            ? 'UNSIGNED'
            : 'INTEGER';

        $query
            ->orderByRaw(
                "CASE WHEN {$quoted} IS NULL THEN 1 ELSE 0 END"
            )
            ->orderByRaw(
                "CAST({$quoted} AS {$integer}) {$direction}"
            )
            ->orderByRaw(
                "{$quoted} {$direction}"
            );
    }
}
