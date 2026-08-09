<?php

namespace App\Services\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Builder;

final class PlanificationAccessService
{
    public function authorizedProjects(): Builder
    {
        return Project::query()
            ->whereIn('company_id', $this->allowedCompanyIds());
    }

    public function allowedCompanyIds(): Builder
    {
        return auth()
            ->user()
            ->companiesForPermissionQuery(ProjectPermissionEnum::View)
            ->select('companies.id');
    }

    public function authorizedProjectMilestone(int $id): ProjectMilestone
    {
        return ProjectMilestone::query()
            ->whereKey($id)
            ->whereHas(
                'project',
                fn (Builder $query) => $query
                    ->whereIn('company_id', $this->allowedCompanyIds())
            )
            ->with([
                'milestone:id,name,code,color,view_color',
                'project:id,name,company_id,forecast_start_date',
            ])
            ->firstOrFail();
    }

    public function canExport(): bool
    {
        return auth()
            ->user()
            ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
            ->exists() ?? false;
    }
}
