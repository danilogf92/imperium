<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BrandSetting extends Model
{
    protected $fillable = ['name', 'logo_path', 'accent_color', 'excel_color'];

    public static function accentColor(): string
    {
        return self::validColor(self::current()?->accent_color, '#7DB9F1');
    }

    public static function excelColor(): string
    {
        return self::validColor(self::current()?->excel_color, '#FDBA74');
    }

    public static function current(): ?self
    {
        try {
            return Schema::hasTable('brand_settings') ? self::query()->first() : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function logoUrl(): string
    {
        $setting = self::current();
        $path = $setting?->logo_path;
        $version = $setting?->updated_at?->timestamp ?? 1;

        return filled($path)
            ? Storage::disk('public')->url($path).'?v='.$version
            : asset('images/branding/logo.svg').'?v=1';
    }

    private static function validColor(?string $color, string $fallback): string
    {
        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : $fallback;
    }
}
