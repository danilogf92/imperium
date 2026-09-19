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
    #[\Livewire\Attributes\Locked]
    public ?int $activityProjectId = null;
    #[\Livewire\Attributes\Locked]
    public bool $allProjectActivities = false;
    #[\Livewire\Attributes\Locked]
    public bool $notesOnly = false;
    #[\Livewire\Attributes\Locked]
    public string $activityProjectLabel = '';
    public string $activityPeriod = '';
    public ?int $activityAssigneeId = null;
    public int $activityWeekYear = 0;
    public int $activityWeekNumber = 0;
    public string $weeklyActivity = '';
    #[\Livewire\Attributes\Locked]
    public ?int $activityEditingId = null;
    #[\Livewire\Attributes\Locked]
    public array $weekActivities = [];
    #[\Livewire\Attributes\Locked]
    public ?int $pendingActivityDeleteId = null;
    public string $pendingActivityDeleteLabel = '';
    public string $milestoneCompletionFilter = '';
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

    public function updatedMilestoneCompletionFilter(string $value): void
    {
        if (! in_array($value, ['', 'completed', 'incomplete'], true)) {
            $this->milestoneCompletionFilter = '';
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
            'milestoneCompletionFilter',
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
        $this->allProjectActivities = false;
        $this->notesOnly = false;
        $this->activityProjectLabel = $project->pda_code.' · '.$project->name;
        $this->activityPeriod = $date->format('o-\WW');
        $this->activityAssigneeId = null;
        $this->activityProjectId = $project->id;
        $this->activityWeekYear = (int) $date->isoWeekYear;
        $this->activityWeekNumber = (int) $date->isoWeek;
        $this->weeklyActivity = '';
        $this->activityEditingId = null;
        $this->canEditActivity = $access->canForProject(ProjectPermissionEnum::Update, $project->id);
        $this->canDeleteActivity = $access->canForProject(ProjectPermissionEnum::Delete, $project->id);
        $this->loadWeekActivities();
        $this->resetValidation();
        $this->showActivityModal = true;
        $this->dispatch('open-modal', 'weekly-project-activity');
    }

    public function closeActivityModal(): void
    {
        $this->showActivityModal = false;
        $this->reset([
            'activityProjectId', 'weeklyActivity', 'activityEditingId', 'weekActivities',
            'pendingActivityDeleteId', 'pendingActivityDeleteLabel', 'activityAssigneeId', 'activityPeriod', 'allProjectActivities', 'activityProjectLabel',
        ]);
        $this->canEditActivity = false;
        $this->canDeleteActivity = false;
        $this->resetValidation('weeklyActivity');
        $this->dispatch('close-modal', 'weekly-project-activity');
    }

    public function openProjectActivities(int $projectId, PlanificationAccessService $access): void
    {
        $this->openWeeklyActivity($projectId, 0, $access);
        $this->allProjectActivities = true;
        $this->activityPeriod = '';
        $this->loadWeekActivities();
    }

    public function openProjectNotes(int $projectId, PlanificationAccessService $access): void
    {
        $this->openProjectActivities($projectId, $access);
        $this->notesOnly = true;
        $this->loadWeekActivities();
    }

    public function openAssignedActivity(int $activityId, PlanificationAccessService $access): void
    {
        $activity = auth()->user()->assignedPlanificationActivities()->findOrFail($activityId);
        $this->openProjectActivities($activity->project_id, $access);
        auth()->user()->unreadPlanificationAssignments()->where('data->activity_id', $activityId)->update(['read_at' => now()]);
        $this->dispatch('planification-notifications-updated');
    }

    public function cancelEditWeeklyActivity(): void
    {
        $this->reset(['weeklyActivity', 'activityEditingId', 'activityAssigneeId']);
        $this->activityPeriod = $this->allProjectActivities ? '' : sprintf('%04d-W%02d', $this->activityWeekYear, $this->activityWeekNumber);
        $this->resetValidation();
    }

    public function saveWeeklyActivity(PlanificationAccessService $access, \App\Services\Planification\PlanificationActivityService $activities): void
    {
        abort_unless($access->canForProject(ProjectPermissionEnum::Update, (int) $this->activityProjectId), 403);
        $this->weeklyActivity = trim($this->weeklyActivity);
        if ($this->notesOnly) {
            $this->activityPeriod = '';
            $this->activityAssigneeId = null;
            if ($this->activityEditingId) {
                ProjectWeeklyActivity::whereKey($this->activityEditingId)
                    ->where('project_id', $this->activityProjectId)
                    ->whereNull('week_year')->whereNull('assigned_to')->firstOrFail();
            }
        }
        $this->validate([
            'weeklyActivity' => ['required', 'string', 'max:5000'],
            'activityAssigneeId' => ['nullable', 'integer', 'min:1'],
            'activityPeriod' => ['nullable', 'regex:/^20[0-9]{2}-W[0-9]{2}$/'],
        ]);
        $year = $week = null;
        if ($this->activityPeriod !== '') {
            [$year, $week] = array_map('intval', explode('-W', $this->activityPeriod));
            if (CarbonImmutable::now()->setISODate($year, $week)->format('o-\WW') !== $this->activityPeriod) {
                $this->addError('activityPeriod', __('planification_activities.invalid_period'));
                return;
            }
        }
        $project = $access->authorizedProjects()->findOrFail($this->activityProjectId);
        $activities->save($project, $this->activityEditingId, $this->weeklyActivity, $this->activityAssigneeId, $year, $week);
        $this->cancelEditWeeklyActivity();
        $this->loadWeekActivities();
        $this->dispatch('planification-notifications-updated');
        $this->dispatch('alert', type: 'success', title: __($this->notesOnly ? 'notes.saved' : 'planification_activities.saved'), position: 'center', timer: 1800);
    }

    public function editWeeklyActivity(int $activityId, PlanificationAccessService $access): void
    {
        abort_unless($access->canForProject(ProjectPermissionEnum::Update, (int) $this->activityProjectId), 403);
        $activity = ProjectWeeklyActivity::query()->whereKey($activityId)
            ->where('project_id', $this->activityProjectId)->firstOrFail();
        $this->activityEditingId = $activity->id;
        $this->weeklyActivity = $activity->activity;
        $this->activityAssigneeId = $activity->assigned_to;
        $this->activityPeriod = $activity->week_year ? sprintf('%04d-W%02d', $activity->week_year, $activity->week_number) : '';
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
            $this->cancelEditWeeklyActivity();
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
            'milestoneCompletion' => $this->milestoneCompletionFilter,
            'activityExecution' => $this->activityExecutionFilter,
        ]);

        return view('livewire.planification.planification', [
            ...$data,
            'assignedActivities' => auth()->user()->assignedPlanificationActivities()->with(['project:id,pda_code,name', 'author:id,name'])->latest('id')->get(),
            'assignmentNotices' => auth()->user()->planificationAssignments()->get()->keyBy('data.activity_id'),
            'assigneeOptions' => $this->activityProjectId && ($activityProject = $access->authorizedProjects()->find($this->activityProjectId))
                ? app(\App\Services\Planification\PlanificationActivityService::class)->eligibleUsers($activityProject)->orderBy('name')->get(['id', 'name']) : collect(),
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
        $this->dispatch('planification-notifications-updated');
        $this->dispatch('alert', type: 'success', title: __($activity->executed_at
            ? 'planification_activities.completed_feedback'
            : 'planification_activities.reopened_feedback'), position: 'center', timer: 2500);
    }

    private function loadWeekActivities(): void
    {
        $this->weekActivities = ProjectWeeklyActivity::query()
            ->where('project_id', $this->activityProjectId)
            ->when(! $this->allProjectActivities, fn ($query) => $query->where('week_year', $this->activityWeekYear)->where('week_number', $this->activityWeekNumber))
            ->when($this->notesOnly, fn ($query) => $query->whereNull('week_year')->whereNull('assigned_to'))
            ->with(['author:id,name', 'assignee:id,name'])->latest('id')->get()
            ->map(fn (ProjectWeeklyActivity $activity) => [
                'id' => $activity->id, 'activity' => $activity->activity,
                'attribution' => $activity->attribution(),
                'assignee' => $activity->assignee?->name,
                'period' => $activity->periodLabel(),
                'expired' => $activity->week_year && now()->isAfter(CarbonImmutable::now()->setISODate($activity->week_year, $activity->week_number)->endOfWeek()),
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
