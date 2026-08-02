<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'city_id',
        'company_name',
        'company_code',
        'multiplier',
    ];

    protected $casts = [
        'multiplier' => 'decimal:6',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Esta relación puede conservarse si todavía utilizas company_user
     * en alguna otra parte del sistema.
     *
     * El acceso a proyectos se determinará mediante los roles.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}
