<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Livewire\Dashboard\Concerns\InteractsWithDashboardFilters;
use App\Services\Dashboard\DashboardChartService;
use App\Services\Dashboard\DashboardStatisticsService;
use App\Support\Dashboard\DashboardFilters;
use App\Support\DashboardCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    use InteractsWithDashboardFilters;

    private const CACHE_TTL_MINUTES = 60;

    public function mount(DashboardStatisticsService $statisticsService): void
    {
        abort_unless(auth()->check(), 403);
        $this->years = $statisticsService->availableYears(auth()->user());
    }

    public function render(
        DashboardStatisticsService $statisticsService,
        DashboardChartService $chartService
    ): View {
        $this->sanitizeFilters();
        $filters = $this->filters();

        $statistics = Cache::remember(
            $this->cacheKey($filters),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => $statisticsService->load(auth()->user(), $filters)
        );

        return view('livewire.dashboard.dashboard', [
            'companies' => auth()->user()?->availableCompanies() ?? collect(),
            'stateOptions' => $this->reportableStateOptions(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            ...$statistics,
            ...$chartService->build($statistics, $this->currency),
        ])->layout('layouts.app');
    }

    private function cacheKey(DashboardFilters $filters): string
    {
        return 'dashboard:charts-v9:v'.DashboardCache::version().':'.hash(
            'sha256',
            json_encode([
                'user' => auth()->id(),
                ...$filters->cacheData(),
            ], JSON_THROW_ON_ERROR)
        );
    }
}
