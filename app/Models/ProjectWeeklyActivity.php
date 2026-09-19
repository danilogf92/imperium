<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectWeeklyActivity extends Model
{
    use Auditable;

    protected $fillable = ['project_id', 'week_year', 'week_number', 'activity', 'executed_at', 'created_by', 'assigned_to'];

    protected $casts = [
        'week_year' => 'integer',
        'week_number' => 'integer',
        'executed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attribution(): string
    {
        return ($this->created_at?->format('d/m/Y H:i') ?? '').' · '.($this->author?->name ?? __('notes.unknown_author'));
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function periodLabel(): string
    {
        return $this->week_year ? sprintf('%04d-W%02d', $this->week_year, $this->week_number) : __('planification_activities.general');
    }

    public function exportDescription(): string
    {
        return $this->periodLabel().' · '.($this->executed_at ? __('planification_activities.completed') : __('planification_activities.pending'))
            ."\n".__('planification_activities.created_by').': '.$this->attribution()
            ."\n".__('planification_activities.assigned_to').': '.($this->assignee?->name ?? __('planification_activities.unassigned'))
            ."\n".$this->activity;
    }

    public function assignmentNotifications(): \Illuminate\Database\Eloquent\Builder
    {
        return \Illuminate\Notifications\DatabaseNotification::query()
            ->where('type', \App\Notifications\PlanificationActivityAssigned::class)
            ->where('data->activity_id', $this->id);
    }

    protected static function booted(): void
    {
        static::deleted(fn (self $activity) => $activity->assignmentNotifications()->delete());
    }
}
