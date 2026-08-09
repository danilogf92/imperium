<?php

namespace App\Services\Planification;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PlanificationMilestoneService
{
    public function __construct(
        private readonly PlanificationAccessService $access,
    ) {}

    public function prepareCreateAt(int $projectId, int $year, int $month): array
    {
        $project = $this->access
            ->authorizedProjects()
            ->findOrFail($projectId);

        $this->ensureProjectIsOpen($project);

        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->min('cycle_year')
            ?? now()->year;

        abort_unless(
            in_array($year, [$firstYear, $firstYear + 1], true),
            422
        );

        return [
            'projectId' => $project->id,
            'milestoneId' => null,
            'month' => $month,
            'cycleYear' => $year,
            'editingId' => null,
            'percentage' => '0',
        ];
    }

    public function editData(int $projectMilestoneId): array
    {
        $item = $this->access
            ->authorizedProjectMilestone($projectMilestoneId);

        return [
            'editingId' => $item->id,
            'projectId' => $item->project_id,
            'milestoneId' => $item->milestone_id,
            'month' => $item->month,
            'cycleYear' => $item->cycle_year,
            'percentage' => (string) $item->percentage,
        ];
    }

    public function deletePreview(int $projectMilestoneId): array
    {
        $item = $this->access
            ->authorizedProjectMilestone($projectMilestoneId);

        return [
            'id' => $item->id,
            'label' => "{$item->milestone->code} — {$item->project->name}",
        ];
    }

    public function save(array $validated, ?int $editingId): ProjectMilestone
    {
        $project = $this->access
            ->authorizedProjects()
            ->find($validated['projectId']);

        if (! $project) {
            throw ValidationException::withMessages([
                'projectId' => 'You do not have permission to plan this project.',
            ]);
        }

        if (! $editingId) {
            $this->ensureProjectIsOpen($project);
        }

        $selectedMilestone = Milestone::query()
            ->findOrFail($validated['milestoneId']);

        $this->validateBusinessRules(
            $project,
            $selectedMilestone,
            $validated,
            $editingId
        );

        return DB::transaction(function () use ($project, $validated, $editingId): ProjectMilestone {
            $item = $editingId
                ? $this->access->authorizedProjectMilestone($editingId)
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

            return $item;
        });
    }

    public function delete(int $projectMilestoneId): void
    {
        DB::transaction(function () use ($projectMilestoneId): void {
            $item = $this->access
                ->authorizedProjectMilestone($projectMilestoneId);

            $projectId = $item->project_id;

            $item->delete();

            $this->resequenceProject($projectId);
        });
    }

    public function ensureProjectIsOpen(Project $project): void
    {
        $isClosed = $project
            ->projectMilestones()
            ->whereHas(
                'milestone',
                fn (Builder $query) => $query
                    ->whereRaw('UPPER(code) = ?', ['CLOSED'])
            )
            ->exists();

        if ($isClosed) {
            throw ValidationException::withMessages([
                'projectId' => 'This project is closed and cannot receive more milestones.',
            ]);
        }
    }

    private function validateBusinessRules(
        Project $project,
        Milestone $selectedMilestone,
        array $validated,
        ?int $editingId
    ): void {
        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->min('cycle_year')
            ?? (int) $validated['cycleYear'];

        if (! in_array(
            (int) $validated['cycleYear'],
            [$firstYear, $firstYear + 1],
            true
        )) {
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

        $requestedPosition = ((int) $validated['cycleYear'] * 12)
            + (int) $validated['month'];

        $firstPosition = $firstMilestone
            ? ($firstMilestone->cycle_year * 12) + $firstMilestone->month
            : null;

        if (
            $firstPosition !== null
            && $requestedPosition < $firstPosition
            && (! $editingId || $editingId !== $firstMilestone->id)
        ) {
            throw ValidationException::withMessages([
                'month' => 'A milestone cannot be placed before the project plan start month.',
            ]);
        }

        $closedItem = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when(
                $editingId,
                fn (Builder $query) => $query->whereKeyNot($editingId)
            )
            ->whereHas(
                'milestone',
                fn (Builder $query) => $query
                    ->whereRaw('UPPER(code) = ?', ['CLOSED'])
            )
            ->first();

        if ($closedItem && strtoupper($selectedMilestone->code) !== 'CLOSED') {
            $closedPosition = ($closedItem->cycle_year * 12)
                + $closedItem->month;

            if (! $editingId || $requestedPosition > $closedPosition) {
                throw ValidationException::withMessages([
                    'milestoneId' => 'Milestones cannot be added or moved after Closed Project.',
                ]);
            }
        }

        if (strtoupper($selectedMilestone->code) === 'CLOSED') {
            $hasLaterItems = ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->when(
                    $editingId,
                    fn (Builder $query) => $query->whereKeyNot($editingId)
                )
                ->whereRaw(
                    '(cycle_year * 12) + month > ?',
                    [$requestedPosition]
                )
                ->exists();

            if ($hasLaterItems) {
                throw ValidationException::withMessages([
                    'month' => 'Closed Project must be the final milestone in the timeline.',
                ]);
            }
        }

        $allocatedPercentage = (float) ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when(
                $editingId,
                fn (Builder $query) => $query->whereKeyNot($editingId)
            )
            ->sum('percentage');

        if ($allocatedPercentage + (float) $validated['percentage'] > 100.00001) {
            throw ValidationException::withMessages([
                'percentage' => 'The project milestone percentages cannot exceed 100%.',
            ]);
        }
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
            ->each(
                fn (ProjectMilestone $item, int $index) =>
                    $item->update(['sequence' => $index + 1])
            );
    }
}
