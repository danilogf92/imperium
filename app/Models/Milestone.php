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
        'view_color',
    ];

    public function viewTextColor(): string
    {
        $color = ltrim($this->view_color ?: $this->color, '#');
        [$red, $green, $blue] = array_map('hexdec', str_split($color, 2));

        return (($red * 299) + ($green * 587) + ($blue * 114)) / 1000 > 150
            ? '#0F172A'
            : '#FFFFFF';
    }

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }
}
