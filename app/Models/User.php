<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\ProjectPermissionEnum;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;

    protected array $auditExclude = [
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'password',
        'is_active',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        $version = $this->updated_at?->getTimestamp() ?? time();

        return '/storage/' . ltrim($this->profile_photo_path, '/') . '?v=' . $version;
    }

    /**
     * Relación directa entre usuarios y compañías.
     *
     * Esta relación solamente debe utilizarse si realmente
     * asignas compañías mediante la tabla pivote company_user.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * Área asignada al usuario.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Proyectos creados por el usuario.
     */
    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * Proyectos de los que el usuario es responsable.
     */
    public function responsibleProjects()
    {
        return $this->hasMany(Project::class, 'responsible_id');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Retorna la consulta de las compañías permitidas
     * para el usuario mediante sus roles.
     */
    public function availableCompaniesQuery(): Builder
    {
        $companyIds = $this->roles()
            ->whereNotNull('company_id')
            ->select('company_id');

        return Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('company_name');
    }

    /**
     * Retorna las compañías permitidas para el usuario.
     */
    public function availableCompanies(): Collection
    {
        return $this->availableCompaniesQuery()
            ->get([
                'id',
                'company_name',
                'company_code',
            ]);
    }

    /**
     * Retorna solamente los códigos de las compañías
     * permitidas para el usuario.
     *
     * @return array<int, string>
     */
    public function availableCompanyCodes(): array
    {
        return $this->availableCompaniesQuery()
            ->whereNotNull('company_code')
            ->pluck('company_code')
            ->map(
                fn(mixed $companyCode): string => (string) $companyCode
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Verifica si el usuario tiene acceso a una compañía
     * mediante el ID.
     */
    public function canAccessCompany(int $companyId): bool
    {
        return $this->availableCompaniesQuery()
            ->whereKey($companyId)
            ->exists();
    }

    /**
     * Verifica si el usuario tiene acceso a una compañía
     * mediante su código.
     */
    public function canAccessCompanyCode(string $companyCode): bool
    {
        return $this->availableCompaniesQuery()
            ->where('company_code', $companyCode)
            ->exists();
    }

    public function companiesForPermissionQuery(
        ProjectPermissionEnum|string $permission
    ): Builder {
        $permissionName = $permission instanceof ProjectPermissionEnum
            ? $permission->value
            : $permission;

        $companyIds = $this->roles()
            ->whereNotNull('company_id')
            ->whereHas(
                'permissions',
                fn(Builder $query): Builder => $query->where(
                    'name',
                    $permissionName
                )
            )
            ->select('company_id');

        return Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('company_name');
    }

    public function companiesForPermission(
        ProjectPermissionEnum|string $permission
    ): Collection {
        return $this->companiesForPermissionQuery($permission)
            ->get(['id', 'company_name', 'company_code']);
    }

    /** @return array<int, int> */
    public function companyIdsForPermission(
        ProjectPermissionEnum|string $permission
    ): array {
        return $this->companiesForPermissionQuery($permission)
            ->pluck('id')
            ->map(fn(mixed $id): int => (int) $id)
            ->all();
    }

    public function hasPermissionInCompany(
        ProjectPermissionEnum|string $permission,
        int $companyId
    ): bool {
        return $this->companiesForPermissionQuery($permission)
            ->whereKey($companyId)
            ->exists();
    }
}
