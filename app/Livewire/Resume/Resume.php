<?php

namespace App\Livewire\Resume;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Exports\ProjectResumeExport;
use App\Models\Project;
use App\Services\Resume\ResumeChartService;
use App\Support\ChartValueFormatter;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Resume extends Component
{
    public string $search = '';

    public array $plantFilter = [];

    public array $yearFilter = [];

    public array $stateFilter = [];

    public array $investmentFilter = [];

    public array $classificationFilter = [];

    public string $currency = 'euro';

    public function mount(): void
    {
        abort_unless(auth()->user(), 403);
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'plantFilter',
            'yearFilter',
            'stateFilter',
            'investmentFilter',
            'classificationFilter',
            'currency',
        ]);
    }

    public function updatedCurrency(): void
    {
        if (! in_array($this->currency, ['euro', 'dollar'], true)) {
            $this->currency = 'euro';
        }
    }

    #[Renderless]
    public function exportExcel(): BinaryFileResponse
    {
        $user = auth()->user();
        abort_unless(
            $user?->companiesForPermissionQuery(ProjectPermissionEnum::Export)->exists(),
            403
        );

        return (new ProjectResumeExport)->download(
            $this->summaryRows(ProjectPermissionEnum::Export),
            $this->activeFilterLabels(),
            $this->currency === 'dollar' ? '$' : "\u{20AC}"
        );
    }

    public function render(ResumeChartService $chartService): View
    {
        $rows = $this->summaryRows(ProjectPermissionEnum::View);
        $companies = auth()->user()
            ->companiesForPermission(ProjectPermissionEnum::View);
        $years = $this->baseProjectQuery(ProjectPermissionEnum::View)
            ->whereNotNull('forecast_start_date')
            ->selectRaw('YEAR(forecast_start_date) AS year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->map(fn ($year): string => (string) $year)
            ->values();

        $stackedChart = $rows->reduce(
            function ($chart, array $row) {
                return $chart
                    ->addSeriesColumn('Booked', (string) $row['year'], $row['booked'])
                    ->addSeriesColumn('Executed', (string) $row['year'], $row['executed'])
                    ->addSeriesColumn('Available', (string) $row['year'], $row['available']);
            },
            LivewireCharts::multiColumnChartModel()
                ->setTitle('Annual financial position')
                ->stacked()
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->legendPositionTop()
                ->setAnimated(true)
                ->setJsonConfig($this->moneyChartConfig())
        );

        $projectsChartOptions = $this->projectsChartOptions($rows);
        $comparisonChartOptions = $this->comparisonChartOptions($rows);
        $cashFlowChartOptions = $this->cashFlowChartOptions(ProjectPermissionEnum::View);

        $availableChart = $rows->reduce(
            function ($chart, array $row) {
                return $chart
                    ->addSeriesPoint('Available', (string) $row['year'], $row['available'])
                    ->addSeriesPoint('Booked', (string) $row['year'], $row['booked'])
                    ->addSeriesPoint('Budgeted', (string) $row['year'], $row['budgeted'])
                    ->addSeriesPoint('Approved', (string) $row['year'], $row['approved']);
            },
            LivewireCharts::multiLineChartModel()
                ->setTitle('Annual financial trend')
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->legendPositionTop()
                ->setAnimated(true)
                ->setJsonConfig($this->moneyChartConfig())
        );

        $currencySymbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";

        return view('livewire.resume.resume', [
            'rows' => $rows,
            'stackedChart' => $stackedChart,
            'comparisonChartOptions' => $comparisonChartOptions,
            'projectsChartOptions' => $projectsChartOptions,
            'cashFlowChartOptions' => $cashFlowChartOptions,
            'availableChart' => $availableChart,
            'companies' => $companies,
            'years' => $years,
            'stateOptions' => $this->reportableStateOptions(),
            'investmentOptions' => InvestmentEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'canExport' => auth()->user()
                ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                ->exists() ?? false,
            'hasActiveFilters' => $this->hasActiveFilters(),
            'currencySymbol' => $currencySymbol,
            ...$chartService->additionalCharts(
                $rows,
                $currencySymbol,
                $this->coverageRowsByApprovalYear(ProjectPermissionEnum::View)
            ),
        ])->layout('layouts.app');
    }

    private function summaryRows(ProjectPermissionEnum $permission): Collection
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $bookedColumn = $this->currency === 'dollar' ? 'booked' : 'booked_euros';
        $executedColumn = $this->currency === 'dollar' ? 'executed_dollars' : 'executed_euros';

        return $this->filteredProjectQuery($permission)
            ->with('company:id,company_name')
            ->withSum('data as original_budget', $budgetColumn)
            ->withSum('data as booked', $bookedColumn)
            ->withSum('data as executed', $executedColumn)
            ->whereNotNull('forecast_start_date')
            ->orderBy('forecast_start_date')
            ->get()
            ->groupBy(fn (Project $project): int => (int) $project->forecast_start_date?->year)
            ->map(function (Collection $projects, int $year): array {
                $original = round((float) $projects->sum('original_budget'), 2);
                $approved = round((float) $projects->sum(
                    fn (Project $project): float => in_array(
                        $project->state?->value,
                        [ProjectStateEnum::Execution->value, ProjectStateEnum::Finished->value],
                        true
                    ) ? (float) $project->original_budget : 0
                ), 2);
                $executed = round((float) $projects->sum('executed'), 2);
                $booked = round((float) $projects->sum('booked'), 2);

                return [
                    'year' => $year,
                    'project_count' => $projects->count(),
                    'budgeted' => $original,
                    'approved' => $approved,
                    'booked' => $booked,
                    'executed' => $executed,
                    'available' => round($approved - $booked, 2),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function coverageRowsByApprovalYear(ProjectPermissionEnum $permission): Collection
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $bookedColumn = $this->currency === 'dollar' ? 'booked' : 'booked_euros';
        $executedColumn = $this->currency === 'dollar' ? 'executed_dollars' : 'executed_euros';

        return $this->filteredProjectQuery($permission)
            ->withSum('data as original_budget', $budgetColumn)
            ->withSum('data as booked', $bookedColumn)
            ->withSum('data as executed', $executedColumn)
            ->whereNotNull('approve_date')
            ->orderBy('approve_date')
            ->get()
            ->groupBy(fn (Project $project): int => (int) $project->approve_date?->year)
            ->map(function (Collection $projects, int $year): array {
                $budgeted = round((float) $projects->sum('original_budget'), 2);
                $approved = round((float) $projects->sum(
                    fn (Project $project): float => in_array(
                        $project->state?->value,
                        [ProjectStateEnum::Execution->value, ProjectStateEnum::Finished->value],
                        true
                    ) ? (float) $project->original_budget : 0
                ), 2);

                return [
                    'year' => $year,
                    'budgeted' => $budgeted,
                    'approved' => $approved,
                    'booked' => round((float) $projects->sum('booked'), 2),
                    'executed' => round((float) $projects->sum('executed'), 2),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function filteredProjectQuery(ProjectPermissionEnum $permission): Builder
    {
        $query = $this->baseProjectQuery($permission);
        $search = trim($this->search);

        return $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $term = "%{$search}%";
                    $query->where('name', 'like', $term)
                        ->orWhere('pda_code', 'like', $term);
                });
            })
            ->when($this->plantFilter !== [], fn (Builder $query) => $query->whereIn('company_id', $this->plantFilter))
            ->when($this->yearFilter !== [], function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    foreach ($this->yearFilter as $year) {
                        $query->orWhereYear('forecast_start_date', (int) $year);
                    }
                });
            })
            ->when($this->stateFilter !== [], fn (Builder $query) => $query->whereIn('state', $this->stateFilter))
            ->when($this->investmentFilter !== [], fn (Builder $query) => $query->whereIn('investments', $this->investmentFilter))
            ->when(
                $this->classificationFilter !== [],
                fn (Builder $query) => $query->whereIn('classification_of_investments', $this->classificationFilter)
            );
    }

    private function baseProjectQuery(ProjectPermissionEnum $permission): Builder
    {
        $user = auth()->user();
        abort_unless($user, 403);

        return Project::query()
            ->where('state', '<>', ProjectStateEnum::Postponed->value)
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery($permission)
                    ->select('companies.id')
                    ->reorder()
            );
    }

    private function reportableStateOptions(): array
    {
        return array_values(array_filter(
            ProjectStateEnum::cases(),
            fn (ProjectStateEnum $state): bool => $state !== ProjectStateEnum::Postponed
        ));
    }

    private function hasActiveFilters(): bool
    {
        return trim($this->search) !== ''
            || $this->plantFilter !== []
            || $this->yearFilter !== []
            || $this->stateFilter !== []
            || $this->investmentFilter !== []
            || $this->classificationFilter !== []
            || $this->currency !== 'euro';
    }

    private function activeFilterLabels(): array
    {
        return [
            'Search' => trim($this->search) ?: 'All',
            'Plants' => $this->plantFilter === [] ? 'All' : implode(', ', $this->plantFilter),
            'Years' => $this->yearFilter === [] ? 'All' : implode(', ', $this->yearFilter),
            'States' => $this->stateFilter === [] ? 'All' : implode(', ', $this->stateFilter),
            'Investments' => $this->investmentFilter === [] ? 'All' : implode(', ', $this->investmentFilter),
            'Classifications' => $this->classificationFilter === [] ? 'All' : implode(', ', $this->classificationFilter),
            'Currency' => $this->currency === 'dollar' ? 'USD' : 'EUR',
        ];
    }

    private function moneyChartConfig(): array
    {
        $formatter = ChartValueFormatter::compactMoney(
            $this->currency === 'dollar' ? '$' : "\u{20AC}"
        );

        return [
            'yaxis.min' => 0,
            'yaxis.labels.formatter' => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function projectsChartOptions(Collection $rows): array
    {
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $moneyFormatter = ChartValueFormatter::compactMoney($symbol);
        $financialAxisMaximum = $this->niceAxisMaximum(max(
            (float) $rows->max('budgeted'),
            (float) $rows->max('booked'),
            (float) $rows->max('executed'),
        ));
        $projectsAxisMaximum = max(1, (int) ceil((float) $rows->max('project_count')));
        $financialAxis = [
            'min' => 0,
            'max' => $financialAxisMaximum,
            'tickAmount' => 5,
            'forceNiceScale' => false,
        ];

        return [
            'series' => [
                ['name' => 'Projects', 'type' => 'column', 'data' => $rows->pluck('project_count')->values()->all()],
                ['name' => 'Budgeted', 'type' => 'line', 'data' => $rows->pluck('budgeted')->values()->all()],
                ['name' => 'Booked', 'type' => 'line', 'data' => $rows->pluck('booked')->values()->all()],
                ['name' => 'Executed', 'type' => 'line', 'data' => $rows->pluck('executed')->values()->all()],
            ],
            'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#4F46E5', '#0EA5E9', '#F59E0B', '#009E0B'],
            'stroke' => ['width' => [0, 4, 4, 4], 'curve' => 'smooth'],
            'plotOptions' => ['bar' => ['columnWidth' => '48%', 'borderRadius' => 4]],
            'dataLabels' => ['enabled' => true, 'enabledOnSeries' => [0]],
            'xaxis' => ['categories' => $rows->pluck('year')->map(fn ($year) => (string) $year)->values()->all()],
            'yaxis' => [
                [
                    'seriesName' => 'Projects',
                    'title' => ['text' => 'Projects'],
                    'decimalsInFloat' => 0,
                    'min' => 0,
                    'max' => $projectsAxisMaximum,
                    'tickAmount' => min(5, $projectsAxisMaximum),
                    'forceNiceScale' => false,
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Budgeted',
                    'opposite' => true,
                    'title' => ['text' => "Financial value ({$symbol})"],
                    'labels' => [
                        'formatter' => $moneyFormatter,
                        'minWidth' => 70,
                        'maxWidth' => 110,
                    ],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Booked',
                    'opposite' => true,
                    'show' => false,
                    'labels' => ['show' => false],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Executed',
                    'opposite' => true,
                    'show' => false,
                    'labels' => ['show' => false],
                ],
            ],
            'tooltip' => ['y' => ['formatter' => "function(value, context) { if (context.seriesIndex === 0) return Number(value).toLocaleString() + ' projects'; return ({$moneyFormatter})(value); }"]],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }

    private function niceAxisMaximum(float $value): float
    {
        if ($value <= 0) {
            return 1;
        }

        $magnitude = 10 ** floor(log10($value));
        $normalized = $value / $magnitude;
        $niceNormalized = match (true) {
            $normalized <= 1 => 1,
            $normalized <= 2 => 2,
            $normalized <= 5 => 5,
            default => 10,
        };

        return $niceNormalized * $magnitude;
    }

    private function cashFlowChartOptions(ProjectPermissionEnum $permission): array
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $formatter = ChartValueFormatter::compactMoney($symbol);

        $monthlyValues = $this->filteredProjectQuery($permission)
            ->withSum('data as milestone_budget', $budgetColumn)
            ->with(['projectMilestones:id,project_id,cycle_year,month,percentage'])
            ->whereHas('projectMilestones')
            ->get()
            ->flatMap(function (Project $project): Collection {
                $budget = (float) $project->milestone_budget;

                return $project->projectMilestones->map(fn ($milestone): array => [
                    'period' => sprintf('%04d-%02d', $milestone->cycle_year, $milestone->month),
                    'value' => $budget * ((float) $milestone->percentage / 100),
                ]);
            })
            ->groupBy('period')
            ->map(fn (Collection $items): float => round((float) $items->sum('value'), 2))
            ->sortKeys();

        $categories = $monthlyValues->keys()
            ->map(fn (string $period): string => \Carbon\CarbonImmutable::createFromFormat('!Y-m', $period)->format('M Y'))
            ->values()
            ->all();

        return [
            'series' => [['name' => 'Milestone cash flow', 'data' => $monthlyValues->values()->all()]],
            'chart' => ['type' => 'bar', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#0F766E'],
            'plotOptions' => ['bar' => ['columnWidth' => '58%', 'borderRadius' => 5]],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $categories,
                'title' => ['text' => 'Milestone month'],
                'labels' => ['rotate' => -45, 'hideOverlappingLabels' => true],
            ],
            'yaxis' => [
                'min' => 0,
                'title' => ['text' => "Cash flow ({$symbol})"],
                'labels' => ['formatter' => $formatter, 'minWidth' => 80, 'maxWidth' => 120],
            ],
            'tooltip' => ['y' => ['formatter' => $formatter]],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }

    private function comparisonChartOptions(Collection $rows): array
    {
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $formatter = ChartValueFormatter::compactMoney($symbol);

        $financialAxisMaximum = $this->niceAxisMaximum(max(
            (float) $rows->max('budgeted'),
            (float) $rows->max('approved'),
            (float) $rows->max('booked'),
            (float) $rows->max('executed'),
        ));

        $financialAxis = [
            'min' => 0,
            'max' => $financialAxisMaximum,
            'tickAmount' => 5,
            'forceNiceScale' => false,
        ];

        return [
            'series' => [
                ['name' => 'Budgeted', 'data' => $rows->pluck('budgeted')->values()->all()],
                ['name' => 'Approved', 'data' => $rows->pluck('approved')->values()->all()],
                ['name' => 'Booked', 'data' => $rows->pluck('booked')->values()->all()],
                ['name' => 'Executed', 'data' => $rows->pluck('executed')->values()->all()],
            ],
            'chart' => ['type' => 'area', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#2563EB', '#8B5CF6', '#F59E0B', '#10B981'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'fill' => [
                'type' => 'gradient',
                'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.35, 'opacityTo' => 0.04],
            ],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $rows->pluck('year')
                    ->map(fn ($year) => (string) $year)
                    ->values()
                    ->all(),
            ],
            'yaxis' => [
                [
                    ...$financialAxis,
                    'seriesName' => 'Budgeted',
                    'title' => ['text' => "Financial value ({$symbol})"],
                    'labels' => ['show' => true, 'formatter' => $formatter, 'minWidth' => 80, 'maxWidth' => 130],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Approved',
                    'show' => false,
                    'labels' => ['show' => false],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Booked',
                    'show' => false,
                    'labels' => ['show' => false],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => 'Executed',
                    'show' => false,
                    'labels' => ['show' => false],
                ],
            ],
            'tooltip' => ['y' => ['formatter' => $formatter]],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }
}
