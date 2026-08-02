<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExcelTemplate extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'template_key',
        'category',
        'description',
        'disk',
        'file_path',
        'original_file_name',
        'is_active',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
