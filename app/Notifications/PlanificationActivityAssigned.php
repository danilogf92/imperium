<?php

namespace App\Notifications;

use App\Models\ProjectWeeklyActivity;
use App\Models\User;
use Illuminate\Notifications\Notification;

class PlanificationActivityAssigned extends Notification
{
    public function __construct(public ProjectWeeklyActivity $activity, public User $assignedBy) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'activity_id' => $this->activity->id,
            'project_id' => $this->activity->project_id,
            'project' => $this->activity->project->pda_code.' · '.$this->activity->project->name,
            'activity' => $this->activity->activity,
            'period' => $this->activity->periodLabel(),
            'assigned_by_id' => $this->assignedBy->id,
            'assigned_by' => $this->assignedBy->name,
        ];
    }
}
