<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class ExcelTemplate extends Model
{
    use Auditable;

    public const FILE_TYPES = [
        'pdf' => 'application/pdf',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppt' => 'application/vnd.ms-powerpoint',
    ];

    protected $fillable = [
        'name',
        'template_key',
        'category',
        'description',
        'disk',
        'file_path',
        'original_file_name',
        'is_active',
        'is_global',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_global' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ExcelTemplate $template): void {
            if (auth()->check()) {
                $template->uploaded_by = auth()->id();
            }
        });

        static::updated(function (ExcelTemplate $template): void {
            $oldPath = $template->getOriginal('file_path');
            $oldDisk = $template->getOriginal('disk') ?: 'local';

            if (
                $oldPath
                && $oldPath !== $template->file_path
                && str_starts_with($oldPath, 'excel-templates/')
            ) {
                Storage::disk($oldDisk)->delete($oldPath);
            }
        });

        static::deleted(function (ExcelTemplate $template): void {
            if (str_starts_with($template->file_path, 'excel-templates/')) {
                Storage::disk($template->disk)->delete($template->file_path);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_excel_template');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $visibility) => $visibility
            ->where('is_global', true)
            ->orWhereHas('companies', fn (Builder $companies) => $companies->whereIn(
                'companies.id', $user->availableCompaniesQuery()->select('companies.id')->reorder()
            )));
    }

    public function fileType(): string
    {
        return strtolower(pathinfo($this->original_file_name, PATHINFO_EXTENSION));
    }

    public static function activeFor(string $key): ?self
    {
        return static::query()
            ->active()
            ->where('template_key', $key)
            ->first();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }
}
