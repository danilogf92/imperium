<?php

namespace App\Services\Task;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class TaskTableQueryService
{
    public function filtered(array $filters): Builder
    {
        return $this->accessibleData()
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $term = '%'.$filters['search'].'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('description', 'like', $term)
                        ->orWhere('qty', 'like', $term)
                        ->orWhere('real_value', 'like', $term)
                        ->orWhere('global_price', 'like', $term)
                        ->orWhere('booked', 'like', $term)
                        ->orWhere('percentage', 'like', $term)
                        ->orWhere('supplier', 'like', $term)
                        ->orWhere('order_no', 'like', $term)
                        ->orWhereHas('project', fn (Builder $project) =>
                            $project->where('pda_code', 'like', $term));
                });
            })
            ->when($filters['statuses'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    foreach ($filters['statuses'] as $status) {
                        $query->orWhere(fn (Builder $item) => $this->applyStatus($item, $status));
                    }
                });
            })
            ->when($filters['orders'] !== [], fn (Builder $query) =>
                $query->whereIn('order_no', $filters['orders']))
            ->when($filters['suppliers'] !== [], fn (Builder $query) =>
                $query->whereIn('supplier', $filters['suppliers']))
            ->when($filters['projects'] !== [], fn (Builder $query) =>
                $query->whereHas('project', fn (Builder $project) =>
                    $project->whereIn('pda_code', $filters['projects'])))
            ->when($filters['years'] !== [], function (Builder $query) use ($filters): void {
                $query->whereHas('project', function (Builder $project) use ($filters): void {
                    $project->where(function (Builder $query) use ($filters): void {
                        foreach ($filters['years'] as $year) {
                            $query->orWhereYear('forecast_start_date', $year);
                        }
                    });
                });
            });
    }

    public function options(): array
    {
        return [
            'supplierOptions' => $this->distinctDataValues('supplier'),
            'orderNumberOptions' => $this->distinctDataValues('order_no'),
            'pdaOptions' => $this->accessibleProjects()
                ->whereNotNull('pda_code')->where('pda_code', '<>', '')
                ->distinct()->orderBy('pda_code')->pluck('pda_code')->all(),
            'years' => $this->accessibleProjects()
                ->whereNotNull('forecast_start_date')->pluck('forecast_start_date')
                ->map(fn ($date) => $date->format('Y'))->unique()->sortDesc()->values(),
        ];
    }

    public function authorizedData(int $id, ProjectPermissionEnum $permission): Data
    {
        $data = Data::query()->with('project')->findOrFail($id);
        abort_unless(auth()->user()?->hasPermissionInCompany(
            $permission, (int) $data->project->company_id
        ), 403);

        return $data;
    }

    private function accessibleData(): Builder
    {
        return Data::query()->whereHas('project', fn (Builder $query) =>
            $query->whereIn('company_id', $this->allowedCompanyIds()));
    }

    private function accessibleProjects(): Builder
    {
        return Project::query()->whereIn('company_id', $this->allowedCompanyIds());
    }

    private function allowedCompanyIds(): Builder
    {
        return auth()->user()->companiesForPermissionQuery(ProjectPermissionEnum::View)
            ->select('companies.id')->reorder();
    }

    private function distinctDataValues(string $column): array
    {
        return $this->accessibleData()->whereNotNull($column)->where($column, '<>', '')
            ->distinct()->orderBy($column)->pluck($column)->all();
    }

    private function applyStatus(Builder $query, string $status): void
    {
        match ($status) {
            'completed' => $query->where('percentage', 100)->whereNotNull('supplier'),
            'progress' => $query->whereBetween('percentage', [0, 99])->whereNotNull('supplier'),
            'pending' => $query->whereNull('supplier'),
            default => null,
        };
    }
}
