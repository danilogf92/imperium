<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    protected static function booted(): void
    {
        static::updated(function (Company $company): void {
            if (! $company->wasChanged('multiplier')) {
                return;
            }

            $company->projects()->update([
                'budgeted' => DB::raw('ROUND(base_budgeted * '.(float) $company->multiplier.', 2)'),
                'budgeted_euros' => DB::raw('ROUND(base_budgeted_euros * '.(float) $company->multiplier.', 2)'),
            ]);
        });
    }

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
