<?php

namespace App\Livewire\Data\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;

trait AuthorizesProjectData
{
    private function authorizedData(
        int $dataId,
        ProjectPermissionEnum $permission
    ): Data {
        $this->authorizeProjectData($permission);

        return Data::query()
            ->where(
                'project_id',
                $this->project->id
            )
            ->findOrFail($dataId);
    }

    private function authorizeProjectData(
        ProjectPermissionEnum $permission
    ): void {
        abort_unless(
            auth()->user()?->hasPermissionInCompany(
                $permission,
                (int) $this->project->company_id
            ),
            403
        );
    }

    private function can(
        ProjectPermissionEnum $permission
    ): bool {
        return auth()->user()?->hasPermissionInCompany(
            $permission,
            (int) $this->project->company_id
        ) ?? false;
    }
}
