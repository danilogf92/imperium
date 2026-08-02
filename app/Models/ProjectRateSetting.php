<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRateSetting extends Model
{
    protected $fillable = ['min_rate', 'max_rate'];

    protected $casts = [
        'min_rate' => 'decimal:4',
        'max_rate' => 'decimal:4',
    ];

    public static function current(): self
    {
        return static::query()->firstOrNew([], [
            'min_rate' => 0.3,
            'max_rate' => 2,
        ]);
    }
}
