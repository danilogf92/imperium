<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectWeeklyActivity extends Model
{
    use Auditable;

    protected $fillable = ['project_id', 'week_year', 'week_number', 'activity'];

    protected $casts = ['week_year' => 'integer', 'week_number' => 'integer'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
