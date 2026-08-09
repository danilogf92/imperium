<?php

namespace App\Livewire\Planification\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Exports\PlanificationExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ExportsPlanification
{
    public function exportExcel(): BinaryFileResponse
    {
        $user = auth()->user();
        abort_unless($user?->companiesForPermissionQuery(ProjectPermissionEnum::Export)->exists(), 403);

        return (new PlanificationExport)->download($user, [
            'search' => $this->search,
            'plants' => $this->plantFilter,
            'statuses' => $this->statusFilter,
            'creationYears' => $this->creationYearFilter,
            'onlyWithMilestones' => $this->onlyWithMilestones,
            'activityWeeks' => $this->activityWeekFilter,
            'currency' => $this->currency,
            'cellDisplay' => $this->cellDisplay,
            'visibleColumns' => $this->visibleColumns,
        ]);
    }
}
