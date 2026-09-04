<?php

namespace App\Services\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Builder;

final class PlanificationAccessService
{
    public function authorizedProjects(ProjectPermissionEnum $permission = ProjectPermissionEnum::View): Builder
    {
        return Project::query()
            ->whereIn('company_id', $this->allowedCompanyIds($permission));
    }

    public function allowedCompanyIds(ProjectPermissionEnum $permission = ProjectPermissionEnum::View): Builder
    {
        return auth()
            ->user()
            ->companiesForPermissionQuery($permission)
            ->select('companies.id');
    }

    public function authorizedProjectMilestone(
        int $id,
        ProjectPermissionEnum $permission = ProjectPermissionEnum::View
    ): ProjectMilestone
    {
        return ProjectMilestone::query()
            ->whereKey($id)
            ->whereHas(
                'project',
                fn (Builder $query) => $query
                    ->whereIn('company_id', $this->allowedCompanyIds($permission))
            )
            ->with([
                'milestone:id,name,code,color,view_color',
                'project:id,name,company_id,forecast_start_date',
            ])
            ->firstOrFail();
    }

    public function can(ProjectPermissionEnum $permission): bool
    {
        return auth()->user()?->companiesForPermissionQuery($permission)->exists() ?? false;
    }

    public function canForProject(ProjectPermissionEnum $permission, int $projectId): bool
    {
        return $this->authorizedProjects($permission)->whereKey($projectId)->exists();
    }

    public function canExport(): bool
    {
        return auth()
            ->user()
            ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
            ->exists() ?? false;
    }
}
