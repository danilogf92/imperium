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

class Project extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'responsible_id',
        'name',
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
}
