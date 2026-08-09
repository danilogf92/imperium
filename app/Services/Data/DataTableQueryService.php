<?php

namespace App\Services\Data;

use App\Models\Data;
use App\Models\Project;
use App\Support\Data\DataTableDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DataTableQueryService
{
    public function filtered(
        Project $project,
        string $search,
        array $filters
    ): Builder {
        $term = trim($search);

        return Data::query()
            ->where('project_id', $project->id)
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $query) use ($term): void {
                    foreach (DataTableDefinition::SEARCH_COLUMNS as $column) {
                        $query->orWhere($column, 'like', "%{$term}%");
                    }
                });
            })
            ->when(
                $filters['areaFilter'] !== [],
                fn (Builder $query) => $query->whereIn('area', $filters['areaFilter'])
            )
            ->when(
                $filters['classificationFilter'] !== [],
                fn (Builder $query) => $query->whereIn(
                    'general_classification',
                    $filters['classificationFilter']
                )
            )
            ->when(
                $filters['itemTypeFilter'] !== [],
                fn (Builder $query) => $query->whereIn(
                    'item_type',
                    $filters['itemTypeFilter']
                )
            )
            ->when(
                $filters['stageFilter'] !== [],
                fn (Builder $query) => $query->whereIn('stage', $filters['stageFilter'])
            )
            ->when(
                $filters['supplierFilter'] !== [],
                fn (Builder $query) => $query->whereIn(
                    'supplier',
                    $filters['supplierFilter']
                )
            )
            ->when(
                $filters['orderYearFilter'] !== [],
                fn (Builder $query) => $query->whereIn('order_year', $filters['orderYearFilter'])
            );
    }

    public function filterOptions(Project $project): Collection
    {
        return collect(DataTableDefinition::FILTER_COLUMNS)
            ->mapWithKeys(fn (string $column, string $filter) => [
                $filter => Data::query()
                    ->where('project_id', $project->id)
                    ->whereNotNull($column)
                    ->where($column, '<>', '')
                    ->distinct()
                    ->orderBy($column)
                    ->pluck($column)
                    ->map(fn ($value) => [
                        'value' => (string) $value,
                        'label' => (string) $value,
                    ])
                    ->values(),
            ]);
    }

    public function hasOrders(Project $project): bool
    {
        return Data::query()
            ->where('project_id', $project->id)
            ->whereNotNull('order_no')
            ->where('order_no', '<>', '')
            ->exists();
    }
}
