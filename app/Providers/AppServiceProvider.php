<?php

namespace App\Providers;

use App\Models\Data;
use App\Models\Project;
use Illuminate\Support\ServiceProvider;
use App\Support\DashboardCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Project::saved(static function (): void {
            DashboardCache::bump();
        });

        Project::deleted(static function (): void {
            DashboardCache::bump();
        });

        Data::saved(static function (): void {
            DashboardCache::bump();
        });

        Data::deleted(static function (): void {
            DashboardCache::bump();
        });
    }
}
