<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'color',
    ];

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }
}
