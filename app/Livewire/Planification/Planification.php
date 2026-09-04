<?php

namespace App\Livewire\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Services\Planification\PlanificationAccessService;
use App\Services\Planification\PlanificationMilestoneService;
use App\Services\Planification\PlanificationQueryService;
use App\Models\ProjectWeeklyActivity;
use App\Livewire\Planification\Concerns\InteractsWithPlanificationColumns;
use App\Livewire\Planification\Concerns\ExportsPlanification;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Planification extends Component
{
    use InteractsWithPlanificationColumns;
    use InteractsWithPerPagePreference;
    use ExportsPlanification;
    use WithPagination;

    public ?int $projectId = null;

    public ?int $milestoneId = null;

    public ?int $month = null;

    public ?int $cycleYear = null;

    public ?int $editingId = null;

    public string $percentage = '0';
    public bool $milestoneExecuted = false;

    public string $currency = 'usd';

    public string $cellDisplay = 'combined';

    public bool $showFormModal = false;

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteLabel = '';

    public string $search = '';

    public int $perPage = 10;

    public array $plantFilter = [];

    public array $statusFilter = [];

    public array $creationYearFilter = [];

    public bool $onlyWithMilestones = false;
    public string $activityWeekFilter = '';
    public bool $showActivityModal = false;
    public ?int $activityProjectId = null;
    public int $activityWeekYear = 0;
    public int $activityWeekNumber = 0;
    public string $weeklyActivity = '';
    public ?int $activityEditingId = null;
    public array $weekActivities = [];
    public ?int $pendingActivityDeleteId = null;
    public string $pendingActivityDeleteLabel = '';
    public string $milestoneExecutionFilter = '';
    public string $activityExecutionFilter = '';
    public bool $canEditActivity = false;
    public bool $canDeleteActivity = false;

    public function updatedProjectId(PlanificationAccessService $access): void
    {
        if ($this->showFormModal && ! $this->editingId && $this->projectId) {
            $project = $access->authorizedProjects()->find($this->projectId);
            $this->cycleYear = $project?->forecast_start_date?->year ?? now()->year;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->savePerPagePreference($this->perPage);
        $this->resetPage();
    }

    public function updatedPlantFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCreationYearFilter(): void
    {
        if (preg_match('/^(\d{4})-W\d{2}$/', $this->activityWeekFilter, $matches)
            && ! in_array((int) $matches[1], array_map('intval', $this->creationYearFilter), true)) {
            $this->activityWeekFilter = '';
        }

        $this->resetPage();
    }

    public function updatedActivityWeekFilter(): void
    {
        if ($this->activityWeekFilter !== '' && ! preg_match('/^\d{4}-W\d{2}$/', $this->activityWeekFilter)) {
            $this->activityWeekFilter = '';
        }

        if (preg_match('/^(\d{4})-W\d{2}$/', $this->activityWeekFilter, $matches)) {
            $this->creationYearFilter = [(int) $matches[1]];
        }

        $this->resetPage();
    }

    public function resetActivityWeekFilter(): void
    {
        $this->activityWeekFilter = '';
        $this->resetPage();
    }

    public function updatedMilestoneExecutionFilter(string $value): void
    {
        if (! in_array($value, ['', 'completed', 'incomplete'], true)) {
            $this->milestoneExecutionFilter = '';
        }
        $this->resetPage();
    }

    public function updatedActivityExecutionFilter(string $value): void
    {
        if (! in_array($value, ['', 'completed', 'incomplete'], true)) {
            $this->activityExecutionFilter = '';
        }
        $this->resetPage();
    }

    public function updatedCurrency(string $value): void
    {
        if (! in_array($value, ['usd', 'eur'], true)) {
            $this->currency = 'usd';
        }
    }

    public function updatedCellDisplay(string $value): void
    {
        if (! in_array($value, ['combined', 'milestone', 'value'], true)) {
            $this->cellDisplay = 'combined';
        }
    }

    public function toggleOnlyWithMilestones(): void
    {
        $this->onlyWithMilestones = ! $this->onlyWithMilestones;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'plantFilter',
            'statusFilter',
            'creationYearFilter',
            'onlyWithMilestones',
            'activityWeekFilter',
            'milestoneExecutionFilter',
            'activityExecutionFilter',
        ]);

        $this->currency = 'usd';
        $this->cellDisplay = 'combined';

        $this->resetPage();
    }

    public function openCreate(PlanificationAccessService $access): void
    {
        abort_unless($access->can(ProjectPermissionEnum::Update), 403);
        $this->resetValidation();

        $this->reset([
            'projectId',
            'milestoneId',
            'month',
            'cycleYear',
            'editingId',
        ]);

        $this->percentage = '0';
        $this->milestoneExecuted = false;
        $this->showFormModal = true;
    }

    public function openCreateAt(int $projectId, int $year, int $month, PlanificationMilestoneService $milestones): void
    {
        $this->resetValidation();
        $this->fill($milestones->prepareCreateAt($projectId, $year, $month));
        $this->showFormModal = true;
    }

    public function editMilestone(int $projectMilestoneId, PlanificationMilestoneService $milestones): void
    {
        $this->resetValidation();
        $this->fill($milestones->editData($projectMilestoneId));
        $this->showFormModal = true;
    }

    public function closeForm(): void
    {
        $this->showFormModal = false;
        $this->resetValidation();
    }

    public function saveMilestone(PlanificationMilestoneService $milestones): void
    {
        if ($this->cycleYear && $this->month
            && CarbonImmutable::create($this->cycleYear, $this->month, 1)
                ->startOfMonth()->isAfter(now()->startOfMonth())) {
            $this->milestoneExecuted = false;
        }

        $validated = $this->validate([
            'projectId' => ['required', 'integer'],
            'milestoneId' => ['required', 'integer', 'exists:milestones,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'cycleYear' => ['required', 'integer', 'between:2000,2200'],
            'percentage' => ['required', 'numeric', 'between:0,100'],
            'milestoneExecuted' => ['boolean'],
        ]);

        $wasEditing = (bool) $this->editingId;

        $milestones->save($validated, $this->editingId);

        $this->showFormModal = false;

        $this->reset(['milestoneId', 'month', 'cycleYear', 'editingId', 'milestoneExecuted']);

        $this->percentage = '0';
        // $this->resetPage();

        session()->flash('planification-status', $wasEditing ? 'Milestone updated successfully.' : 'Milestone added successfully.'
        );
    }

    public function requestDeleteMilestone(int $projectMilestoneId, PlanificationMilestoneService $milestones): void
    {
        $preview = $milestones->deletePreview($projectMilestoneId);
        $this->pendingDeleteId = $preview['id'];
        $this->pendingDeleteLabel = $preview['label'];
    }

    public function cancelDelete(): void
    {
        $this->reset([
            'pendingDeleteId',
            'pendingDeleteLabel',
        ]);
    }

    public function confirmDeleteMilestone(PlanificationMilestoneService $milestones): void
    {
        if (! $this->pendingDeleteId) {
            return;
        }

        $milestones->delete($this->pendingDeleteId);

        $this->reset(['pendingDeleteId', 'pendingDeleteLabel']);

        session()->flash('planification-status', 'Milestone removed successfully.');
    }

    public function openWeeklyActivity(int $projectId, int $weekOffset, PlanificationAccessService $access): void
    {
        $project = $access->authorizedProjects()->findOrFail($projectId);
        $date = $this->selectedActivityWeek()->addWeeks(in_array($weekOffset, [0, 1], true) ? $weekOffset : 0);
        $this->activityProjectId = $project->id;
        $this->activityWeekYear = (int) $date->isoWeekYear;
        $this->activityWeekNumber = (int) $date->isoWeek;
        $this->weeklyActivity = '';
        $this->activityEditingId = null;
        $this->canEditActivity = $access->canForProject(ProjectPermissionEnum::Update, $project->id);
        $this->canDeleteActivity = $access->canForProject(ProjectPermissionEnum::Delete, $project->id);
        $this->loadWeekActivities();
        $this->resetValidation('weeklyActivity');
        $this->showActivityModal = true;
        $this->dispatch('open-modal', 'weekly-project-activity');
    }

    public function closeActivityModal(): void
    {
        $this->showActivityModal = false;
        $this->reset([
            'activityProjectId', 'weeklyActivity', 'activityEditingId', 'weekActivities',
            'pendingActivityDeleteId', 'pendingActivityDeleteLabel',
        ]);
        $this->canEditActivity = false;
        $this->canDeleteActivity = false;
        $this->resetValidation('weeklyActivity');
        $this->dispatch('close-modal', 'weekly-project-activity');
    }

    public function saveWeeklyActivity(PlanificationAccessService $access): void
    {
        abort_unless($access->canForProject(ProjectPermissionEnum::Update, (int) $this->activityProjectId), 403);
        $validated = $this->validate(['weeklyActivity' => ['required', 'string', 'max:5000']]);
        $project = $access->authorizedProjects()->findOrFail($this->activityProjectId);
        $activity = $this->activityEditingId
            ? ProjectWeeklyActivity::query()->whereKey($this->activityEditingId)
                ->where('project_id', $project->id)->firstOrFail()
            : new ProjectWeeklyActivity([
                'project_id' => $project->id, 'week_year' => $this->activityWeekYear,
                'week_number' => $this->activityWeekNumber,
            ]);
        $activity->activity = trim($validated['weeklyActivity']);
        $activity->save();
        $this->reset(['weeklyActivity', 'activityEditingId']);
        $this->loadWeekActivities();
    }

    public function editWeeklyActivity(int $activityId, PlanificationAccessService $access): void
    {
        abort_unless($access->canForProject(ProjectPermissionEnum::Update, (int) $this->activityProjectId), 403);
        $activity = ProjectWeeklyActivity::query()->whereKey($activityId)
            ->where('project_id', $this->activityProjectId)->firstOrFail();
        $this->activityEditingId = $activity->id;
        $this->weeklyActivity = $activity->activity;
        $this->resetValidation('weeklyActivity');
    }

    public function requestDeleteWeeklyActivity(int $activityId, PlanificationAccessService $access): void
    {
        abort_unless($access->canForProject(ProjectPermissionEnum::Delete, (int) $this->activityProjectId), 403);
        $activity = ProjectWeeklyActivity::query()->whereKey($activityId)
            ->where('project_id', $this->activityProjectId)->firstOrFail();
        $this->pendingActivityDeleteId = $activity->id;
        $this->pendingActivityDeleteLabel = str($activity->activity)->limit(120)->toString();
    }

    public function cancelDeleteWeeklyActivity(): void
    {
        $this->reset(['pendingActivityDeleteId', 'pendingActivityDeleteLabel']);
    }

    public function confirmDeleteWeeklyActivity(PlanificationAccessService $access): void
    {
        abort_unless($this->pendingActivityDeleteId, 404);
        abort_unless($access->canForProject(ProjectPermissionEnum::Delete, (int) $this->activityProjectId), 403);
        $activityId = $this->pendingActivityDeleteId;
        ProjectWeeklyActivity::query()->whereKey($activityId)
            ->where('project_id', $this->activityProjectId)->firstOrFail()->delete();
        if ($this->activityEditingId === $activityId) {
            $this->reset(['weeklyActivity', 'activityEditingId']);
        }
        $this->reset(['pendingActivityDeleteId', 'pendingActivityDeleteLabel']);
        $this->loadWeekActivities();
    }

    public function render(PlanificationQueryService $queries, PlanificationAccessService $access): View
    {
        $data = $queries->viewData([
            'search' => $this->search,
            'perPage' => $this->perPage,
            'plants' => $this->plantFilter,
            'statuses' => $this->statusFilter,
            'creationYears' => $this->creationYearFilter,
            'onlyWithMilestones' => $this->onlyWithMilestones,
            'activityWeeks' => $this->activityWeekFilter,
            'milestoneExecution' => $this->milestoneExecutionFilter,
            'activityExecution' => $this->activityExecutionFilter,
        ]);

        return view('livewire.planification.planification', [
            ...$data,
            'fixedColumnOptions' => self::COLUMN_OPTIONS,
            'canUpdatePlanification' => $access->can(ProjectPermissionEnum::Update),
            'editableCompanyIds' => $access->allowedCompanyIds(ProjectPermissionEnum::Update)->pluck('companies.id')->all(),
            'deletableCompanyIds' => $access->allowedCompanyIds(ProjectPermissionEnum::Delete)->pluck('companies.id')->all(),
        ])->layout('layouts.app');
    }

    public function toggleWeeklyActivityExecuted(
        int $activityId,
        PlanificationAccessService $access
    ): void
    {
        $activity = ProjectWeeklyActivity::query()->whereKey($activityId)
            ->where('project_id', $this->activityProjectId)
            ->whereHas('project', fn ($query) => $query->whereIn(
                'company_id',
                $access->allowedCompanyIds(ProjectPermissionEnum::Update)
            ))
            ->firstOrFail();
        $activity->update(['executed_at' => $activity->executed_at ? null : now()]);
        $this->loadWeekActivities();
    }

    private function loadWeekActivities(): void
    {
        $this->weekActivities = ProjectWeeklyActivity::query()
            ->where('project_id', $this->activityProjectId)->where('week_year', $this->activityWeekYear)
            ->where('week_number', $this->activityWeekNumber)->latest('id')->get(['id', 'activity', 'executed_at'])
            ->map(fn (ProjectWeeklyActivity $activity) => [
                'id' => $activity->id,
                'activity' => $activity->activity,
                'executed' => filled($activity->executed_at),
            ])->all();
    }

    private function selectedActivityWeek(): CarbonImmutable
    {
        if (preg_match('/^(\d{4})-W(\d{2})$/', $this->activityWeekFilter, $matches)) {
            return CarbonImmutable::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfDay();
        }

        return CarbonImmutable::now()->startOfWeek();
    }

}
