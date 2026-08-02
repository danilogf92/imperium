<?php

namespace App\Providers;

use App\Models\Data;
use App\Models\Project;
use App\Support\DashboardCache;
use Illuminate\Support\ServiceProvider;

class DashboardCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $invalidate = static fn(): null => tap(null, static fn() => DashboardCache::bump());

        Project::saved($invalidate);
        Project::deleted($invalidate);
        Project::restored($invalidate);

        Data::saved($invalidate);
        Data::deleted($invalidate);
        Data::restored($invalidate);
    }
}
