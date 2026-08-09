<?php

namespace App\Livewire\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Exports\PlanificationExport;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Milestone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Planification extends Component
{
    use WithPagination;

    public ?int $projectId = null;

    public ?int $milestoneId = null;

    public ?int $month = null;

    public ?int $cycleYear = null;

    public ?int $editingId = null;

    public string $percentage = '0';

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

    public function updatedProjectId(): void
    {
        if ($this->showFormModal && ! $this->editingId && $this->projectId) {
            $project = $this->authorizedProjects()->find($this->projectId);
            $this->cycleYear = $project?->forecast_start_date?->year ?? now()->year;
        }

        // $this->resetPage();
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
        $this->reset(['plantFilter', 'statusFilter', 'creationYearFilter', 'onlyWithMilestones']);
        $this->currency = 'usd';
        $this->cellDisplay = 'combined';
        $this->resetPage();
    }

    public function exportExcel(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(ProjectPermissionEnum::Export)->exists(),
            403
        );

        return (new PlanificationExport())->download($user, [
            'search' => $this->search,
            'plants' => $this->plantFilter,
            'statuses' => $this->statusFilter,
            'creationYears' => $this->creationYearFilter,
            'onlyWithMilestones' => $this->onlyWithMilestones,
            'currency' => $this->currency,
            'cellDisplay' => $this->cellDisplay,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset(['projectId', 'milestoneId', 'month', 'cycleYear', 'editingId']);
        $this->percentage = '0';
        $this->showFormModal = true;
    }

    public function openCreateAt(int $projectId, int $year, int $month): void
    {
        $project = $this->authorizedProjects()->findOrFail($projectId);
        $this->ensureProjectIsOpen($project);
        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()->where('project_id', $project->id)->min('cycle_year')
            ?? now()->year;

        abort_unless(in_array($year, [$firstYear, $firstYear + 1], true), 422);

        $this->resetValidation();
        $this->editingId = null;
        $this->projectId = $project->id;
        $this->milestoneId = null;
        $this->month = $month;
        $this->cycleYear = $year;
        $this->percentage = '0';
        $this->showFormModal = true;
    }

    public function editMilestone(int $projectMilestoneId): void
    {
        $item = $this->authorizedProjectMilestone($projectMilestoneId);

        $this->resetValidation();
        $this->editingId = $item->id;
        $this->projectId = $item->project_id;
        $this->milestoneId = $item->milestone_id;
        $this->month = $item->month;
        $this->cycleYear = $item->cycle_year;
        $this->percentage = (string) $item->percentage;
        $this->showFormModal = true;
    }

    public function closeForm(): void
    {
        $this->showFormModal = false;
        $this->resetValidation();
    }

    public function saveMilestone(): void
    {
        $validated = $this->validate([
            'projectId' => ['required', 'integer'],
            'milestoneId' => ['required', 'integer', 'exists:milestones,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'cycleYear' => ['required', 'integer', 'between:2000,2200'],
            'percentage' => ['required', 'numeric', 'between:0,100'],
        ]);

        $project = $this->authorizedProjects()
            ->find($validated['projectId']);

        if (! $project) {
            throw ValidationException::withMessages([
                'projectId' => 'You do not have permission to plan this project.',
            ]);
        }

        if (! $this->editingId) {
            $this->ensureProjectIsOpen($project);
        }

        $selectedMilestone = Milestone::query()->findOrFail($validated['milestoneId']);

        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()->where('project_id', $project->id)->min('cycle_year')
            ?? (int) $validated['cycleYear'];

        if (! in_array((int) $validated['cycleYear'], [$firstYear, $firstYear + 1], true)) {
            throw ValidationException::withMessages([
                'cycleYear' => "A project can only use {$firstYear} and " . ($firstYear + 1) . '.',
            ]);
        }

        $firstMilestone = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->orderBy('cycle_year')
            ->orderBy('month')
            ->orderBy('sequence')
            ->first();

        $requestedPosition = ((int) $validated['cycleYear'] * 12) + (int) $validated['month'];
        $firstPosition = $firstMilestone
            ? ($firstMilestone->cycle_year * 12) + $firstMilestone->month
            : null;

        if (
            $firstPosition !== null
            && $requestedPosition < $firstPosition
            && (! $this->editingId || $this->editingId !== $firstMilestone->id)
        ) {
            throw ValidationException::withMessages([
                'month' => 'A milestone cannot be placed before the project plan start month.',
            ]);
        }

        $closedItem = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when($this->editingId, fn(Builder $query) => $query->whereKeyNot($this->editingId))
            ->whereHas('milestone', fn(Builder $query) => $query->whereRaw('UPPER(code) = ?', ['CLOSED']))
            ->first();

        if ($closedItem && strtoupper($selectedMilestone->code) !== 'CLOSED') {
            $closedPosition = ($closedItem->cycle_year * 12) + $closedItem->month;

            if (! $this->editingId || $requestedPosition > $closedPosition) {
                throw ValidationException::withMessages([
                    'milestoneId' => 'Milestones cannot be added or moved after Closed Project.',
                ]);
            }
        }

        if (strtoupper($selectedMilestone->code) === 'CLOSED') {
            $hasLaterItems = ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->when($this->editingId, fn(Builder $query) => $query->whereKeyNot($this->editingId))
                ->whereRaw('(cycle_year * 12) + month > ?', [$requestedPosition])
                ->exists();

            if ($hasLaterItems) {
                throw ValidationException::withMessages([
                    'month' => 'Closed Project must be the final milestone in the timeline.',
                ]);
            }
        }

        $allocatedPercentage = (float) ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when($this->editingId, fn(Builder $query) => $query->whereKeyNot($this->editingId))
            ->sum('percentage');

        if ($allocatedPercentage + (float) $validated['percentage'] > 100.00001) {
            throw ValidationException::withMessages([
                'percentage' => 'The project milestone percentages cannot exceed 100%.',
            ]);
        }

        DB::transaction(function () use ($project, $validated): void {
            $item = $this->editingId
                ? $this->authorizedProjectMilestone($this->editingId)
                : new ProjectMilestone(['project_id' => $project->id]);

            $item->fill([
                'project_id' => $project->id,
                'milestone_id' => $validated['milestoneId'],
                'month' => (int) $validated['month'],
                'cycle_year' => (int) $validated['cycleYear'],
                'percentage' => (float) $validated['percentage'],
            ]);

            if (! $item->exists) {
                $item->sequence = (int) ProjectMilestone::query()
                    ->where('project_id', $project->id)
                    ->max('sequence') + 1;
            }

            $item->save();
            $this->resequenceProject($project->id);
        });

        $message = $this->editingId ? 'Milestone updated successfully.' : 'Milestone added successfully.';
        $this->showFormModal = false;
        $this->reset(['milestoneId', 'month', 'cycleYear', 'editingId']);
        $this->percentage = '0';
        $this->resetPage();
        session()->flash('planification-status', $message);
    }

    public function requestDeleteMilestone(int $projectMilestoneId): void
    {
        $item = $this->authorizedProjectMilestone($projectMilestoneId);

        $this->pendingDeleteId = $item->id;
        $this->pendingDeleteLabel = "{$item->milestone->code} — {$item->project->name}";
    }

    public function cancelDelete(): void
    {
        $this->reset(['pendingDeleteId', 'pendingDeleteLabel']);
    }

    public function confirmDeleteMilestone(): void
    {
        if (! $this->pendingDeleteId) {
            return;
        }

        $item = $this->authorizedProjectMilestone($this->pendingDeleteId);
        $projectId = $item->project_id;

        $item->delete();
        $this->resequenceProject($projectId);
        $this->reset(['pendingDeleteId', 'pendingDeleteLabel']);
        session()->flash('planification-status', 'Milestone removed successfully.');
    }

    public function render(): View
    {
        $plannedProjects = $this->authorizedProjects()
            ->with([
                'company:id,company_name',
                'projectMilestones' => fn($query) => $query
                    ->with('milestone:id,name,code,color')
                    ->orderBy('cycle_year')
                    ->orderBy('sequence'),
            ])
            ->withSum('data as data_budgeted', 'global_price')
            ->withSum('data as data_budgeted_euros', 'global_price_euros')
            ->when($this->plantFilter !== [], fn(Builder $query) => $query
                ->whereIn('company_id', $this->plantFilter))
            ->when($this->statusFilter !== [], fn(Builder $query) => $query
                ->whereIn('state', $this->statusFilter))
            ->when($this->creationYearFilter !== [], function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    foreach ($this->creationYearFilter as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            })
            ->when($this->onlyWithMilestones, fn(Builder $query) => $query
                ->whereHas('projectMilestones'))
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('pda_code', 'like', $search)
                        ->orWhere('state', 'like', $search)
                        ->orWhereHas('company', fn(Builder $query) => $query
                            ->where('company_name', 'like', $search))
                        ->orWhereHas('projectMilestones.milestone', fn(Builder $query) => $query
                            ->where('name', 'like', $search)
                            ->orWhere('code', 'like', $search));
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $timelineYears = $plannedProjects->getCollection()
            ->flatMap(function (Project $project) {
                $firstYear = $project->forecast_start_date?->year
                    ?? $project->projectMilestones->min('cycle_year')
                    ?? now()->year;

                return $project->projectMilestones
                    ->pluck('cycle_year')
                    ->push($firstYear, $firstYear + 1);
            })
            ->unique()
            ->sort()
            ->values();

        $filterProjects = $this->authorizedProjects()
            ->with('company:id,company_name')
            ->get(['id', 'company_id', 'state', 'forecast_start_date']);

        return view('livewire.planification.planification', [
            'plannedProjects' => $plannedProjects,
            'timelineYears' => $timelineYears,
            'projects' => $this->authorizedProjects()
                ->withExists(['projectMilestones as is_closed' => fn(Builder $query) => $query
                    ->whereHas('milestone', fn(Builder $query) => $query
                        ->whereRaw('UPPER(code) = ?', ['CLOSED']))])
                ->withSum('data as data_budgeted', 'global_price')
                ->withSum('data as data_budgeted_euros', 'global_price_euros')
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'forecast_start_date',
                ]),
            'milestones' => Milestone::query()->orderBy('name')->get(['id', 'name', 'code', 'color']),
            'plantOptions' => $filterProjects
                ->filter(fn(Project $project) => $project->company)
                ->map(fn(Project $project) => [
                    'id' => $project->company_id,
                    'name' => $project->company->company_name,
                ])
                ->unique('id')
                ->sortBy('name')
                ->values(),
            'statusOptions' => ProjectStateEnum::values(),
            'creationYearOptions' => $filterProjects
                ->map(fn(Project $project) => $project->forecast_start_date?->year)
                ->filter()
                ->unique()
                ->sortDesc()
                ->values(),
            'canExport' => auth()->user()
                ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                ->exists() ?? false,
            'months' => [
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ],
        ])->layout('layouts.app');
    }

    private function authorizedProjects(): Builder
    {
        return Project::query()->whereIn('company_id', $this->allowedCompanyIds());
    }

    private function allowedCompanyIds()
    {
        return auth()->user()
            ->companiesForPermissionQuery(ProjectPermissionEnum::View)
            ->select('companies.id');
    }

    private function authorizedProjectMilestone(int $id): ProjectMilestone
    {
        return ProjectMilestone::query()
            ->whereKey($id)
            ->whereHas('project', fn(Builder $query) => $query
                ->whereIn('company_id', $this->allowedCompanyIds()))
            ->firstOrFail();
    }

    private function resequenceProject(int $projectId): void
    {
        ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->increment('sequence', 100000);

        ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->orderBy('cycle_year')
            ->orderBy('month')
            ->orderBy('id')
            ->get()
            ->each(fn(ProjectMilestone $item, int $index) => $item->update(['sequence' => $index + 1]));
    }

    private function ensureProjectIsOpen(Project $project): void
    {
        $isClosed = $project->projectMilestones()
            ->whereHas('milestone', fn(Builder $query) => $query
                ->whereRaw('UPPER(code) = ?', ['CLOSED']))
            ->exists();

        if ($isClosed) {
            throw ValidationException::withMessages([
                'projectId' => 'This project is closed and cannot receive more milestones.',
            ]);
        }
    }
}
