<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectPermissionEnum;
use App\Livewire\Dashboard\Concerns\InteractsWithDashboardFilters;
use App\Services\Dashboard\DashboardPortfolioChartService;
use App\Services\Dashboard\DashboardStatisticsService;
use App\Support\Dashboard\DashboardFilters;
use App\Support\DashboardCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Review extends Component
{
    use InteractsWithDashboardFilters;

    public function mount(DashboardStatisticsService $statisticsService): void
    {
        abort_unless(auth()->check(), 403);
        $this->years = $statisticsService->availableYears(auth()->user());
    }

    public function render(
        DashboardStatisticsService $statisticsService,
        DashboardPortfolioChartService $portfolioCharts
    ): View {
        $this->sanitizeFilters();
        $filters = $this->filters();

        $statistics = Cache::remember(
            $this->cacheKey($filters),
            now()->addHour(),
            fn (): array => $statisticsService->load(auth()->user(), $filters)
        );

        return view('livewire.dashboard.review', [
            'companies' => auth()->user()->companiesForPermission(ProjectPermissionEnum::View),
            'stateOptions' => $this->reportableStateOptions(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            ...$statistics,
            ...$portfolioCharts->build($statistics, $this->currency),
        ])->layout('layouts.app');
    }

    private function cacheKey(DashboardFilters $filters): string
    {
        return 'dashboard:charts-v9:v'.DashboardCache::version().':'.hash(
            'sha256',
            json_encode([
                'user' => auth()->id(),
                'view_company_ids' => auth()->user()->companyIdsForPermission(ProjectPermissionEnum::View),
                ...$filters->cacheData(),
            ], JSON_THROW_ON_ERROR)
        );
    }
}
