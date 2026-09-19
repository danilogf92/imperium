<?php

namespace App\Services\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\ProjectWeeklyActivity;
use App\Models\User;
use App\Notifications\PlanificationActivityAssigned;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanificationActivityService
{
    public function eligibleUsers(Project $project): Builder
    {
        return User::query()->where('is_active', true)->whereHas('roles', fn ($roles) => $roles
            ->where('company_id', $project->company_id)
            ->whereHas('permissions', fn ($permissions) => $permissions->where('name', ProjectPermissionEnum::View->value)));
    }

    public function save(Project $project, ?int $id, string $body, ?int $assignee, ?int $year, ?int $week): ProjectWeeklyActivity
    {
        return DB::transaction(function () use ($project, $id, $body, $assignee, $year, $week) {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            abort_unless(auth()->user()?->hasPermissionInCompany(ProjectPermissionEnum::Update, $project->company_id), 403);
            $recipient = $assignee ? $this->eligibleUsers($project)->find($assignee) : null;
            if ($assignee && ! $recipient) {
                throw ValidationException::withMessages(['activityAssigneeId' => __('planification_activities.invalid_assignee')]);
            }
            $activity = $id ? $project->weeklyActivities()->lockForUpdate()->findOrFail($id)
                : new ProjectWeeklyActivity(['project_id' => $project->id, 'created_by' => auth()->id()]);
            $oldAssignee = $activity->assigned_to;
            $activity->fill(['activity' => $body, 'assigned_to' => $assignee, 'week_year' => $year, 'week_number' => $week]);
            $activity->save();
            if ((int) $oldAssignee !== (int) $assignee) {
                $activity->assignmentNotifications()->delete();
                if ($recipient) {
                    $recipient->notify(new PlanificationActivityAssigned($activity, auth()->user()));
                }
            }

            return $activity;
        });
    }
}
