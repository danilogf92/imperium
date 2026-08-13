<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BrandSetting extends Model
{
    protected $fillable = ['name', 'logo_path'];

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
        $path = self::current()?->logo_path;

        return filled($path)
            ? Storage::disk('public')->url($path)
            : asset('favicon.svg').'?v=3';
    }
}
