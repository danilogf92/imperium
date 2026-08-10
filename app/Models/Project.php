<?php

namespace App\Models;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'company_id',
        'order',
        'created_by',
        'responsible_id',
        'name',
        'slug',
        'pda_code',
        'rate',
        'state',
        'investments',
        'justification',
        'classification_of_investments',
        'data_uploaded',
        'quartile_date',
        'forecast_start_date',
        'forecast_end_date',
        'file_name',
        'upload_pda',
        'project_idea_path',
        'project_idea_name',
        'handover_certificate_path',
        'handover_certificate_name',
        'approve_date',
        'close_date',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'state' => ProjectStateEnum::class,
        'investments' => InvestmentEnum::class,
        'justification' => ProjectJustificationEnum::class,
        'classification_of_investments' => InvestmentClassificationEnum::class,
        'data_uploaded' => 'boolean',
        'quartile_date' => 'date',
        'forecast_start_date' => 'date',
        'forecast_end_date' => 'date',
        'approve_date' => 'date',
        'close_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            if (! $project->isDirty('name') && filled($project->slug)) {
                return;
            }

            $baseSlug = Str::slug($project->name) ?: 'project';
            $slug = $baseSlug;
            $suffix = 2;

            while (static::query()->where('slug', $slug)
                ->when($project->exists, fn ($query) => $query->whereKeyNot($project->getKey()))
                ->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $project->slug = $slug;
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function data(): HasMany
    {
        return $this->hasMany(Data::class);
    }

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function weeklyActivities(): HasMany
    {
        return $this->hasMany(ProjectWeeklyActivity::class);
    }
}
