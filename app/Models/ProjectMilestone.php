<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'month',
        'cycle_year',
        'sequence',
        'percentage',
        'executed_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'cycle_year' => 'integer',
        'sequence' => 'integer',
        'percentage' => 'decimal:2',
        'executed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('cycle_year', '<', now()->year)
                ->orWhere(function (Builder $query): void {
                    $query->where('cycle_year', now()->year)
                        ->where('month', '<=', now()->month);
                });
        });
    }
}
