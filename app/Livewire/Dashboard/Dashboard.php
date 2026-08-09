<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Services\Dashboard\DashboardChartService;
use App\Services\Dashboard\DashboardStatisticsService;
use App\Support\Dashboard\DashboardFilters;
use App\Support\DashboardCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public array $yearSearch = [];

    public array $stateSearch = [];

    public array $typeOfProjectSearch = [];

    public array $investmentSearch = [];

    public array $justificationSearch = [];

    public array $companyFilter = [];

    public array $years = [];

    public string $currency = 'euro';

    private const CACHE_TTL_MINUTES = 60;

    public function mount(DashboardStatisticsService $statisticsService): void
    {
        abort_unless(auth()->check(), 403);
        $this->years = $statisticsService->availableYears(auth()->user());
    }

    public function resetAll(): void
    {
        $this->reset([
            'yearSearch',
            'stateSearch',
            'typeOfProjectSearch',
            'investmentSearch',
            'justificationSearch',
            'companyFilter',
        ]);

        $this->currency = 'euro';
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
            'stateOptions' => ProjectStateEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            ...$statistics,
            ...$chartService->build($statistics, $this->currency),
        ])->layout('layouts.app');
    }

    private function filters(): DashboardFilters
    {
        return new DashboardFilters(
            companies: $this->companyFilter,
            years: $this->yearSearch,
            states: $this->stateSearch,
            classifications: $this->typeOfProjectSearch,
            investments: $this->investmentSearch,
            justifications: $this->justificationSearch,
            currency: $this->currency,
        );
    }

    private function sanitizeFilters(): void
    {
        $this->companyFilter = $this->normalizeSelection(
            $this->companyFilter,
            auth()->user()?->availableCompanyCodes() ?? []
        );

        $this->yearSearch = $this->normalizeSelection($this->yearSearch, $this->years);

        $this->stateSearch = $this->normalizeSelection(
            $this->stateSearch,
            array_column(ProjectStateEnum::cases(), 'value')
        );

        $this->typeOfProjectSearch = $this->normalizeSelection(
            $this->typeOfProjectSearch,
            array_column(InvestmentClassificationEnum::cases(), 'value')
        );

        $this->investmentSearch = $this->normalizeSelection(
            $this->investmentSearch,
            array_column(InvestmentEnum::cases(), 'value')
        );

        $this->justificationSearch = $this->normalizeSelection(
            $this->justificationSearch,
            array_column(ProjectJustificationEnum::cases(), 'value')
        );

        if (! in_array($this->currency, ['euro', 'dollar'], true)) {
            $this->currency = 'euro';
        }
    }

    private function normalizeSelection(array $values, array $allowed): array
    {
        $normalized = array_values(array_unique(array_intersect($values, $allowed)));
        sort($normalized);

        return $normalized;
    }

    private function cacheKey(DashboardFilters $filters): string
    {
        return 'dashboard:v'.DashboardCache::version().':'.hash(
            'sha256',
            json_encode([
                'user' => auth()->id(),
                ...$filters->cacheData(),
            ], JSON_THROW_ON_ERROR)
        );
    }
}
