<?php

namespace App\Livewire\Resume;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Exports\ProjectResumeExport;
use App\Models\Project;
use App\Services\Resume\ResumeChartService;
use App\Support\ChartValueFormatter;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public array $justificationFilter = [];

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
            'justificationFilter',
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
            ->selectRaw(DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y', forecast_start_date) AS year"
                : 'YEAR(forecast_start_date) AS year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->map(fn ($year): string => (string) $year)
            ->values();

        $stackedChart = $rows->reduce(
            function ($chart, array $row) {
                return $chart
                    ->addSeriesColumn(__('Booked (Real SAP)'), (string) $row['year'], $row['booked'])
                    ->addSeriesColumn(__('Committed'), (string) $row['year'], $row['committed'])
                    ->addSeriesColumn(__('Available'), (string) $row['year'], $row['available']);
            },
            LivewireCharts::multiColumnChartModel()
                ->setTitle(__('Annual financial position'))
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
        $cashFlow = $this->cashFlowChartData(ProjectPermissionEnum::View);

        $availableChart = $rows->reduce(
            function ($chart, array $row) {
                return $chart
                    ->addSeriesPoint(__('Budgeted'), (string) $row['year'], $row['budgeted'])
                    ->addSeriesPoint(__('Approved'), (string) $row['year'], $row['approved'])
                    ->addSeriesPoint(__('Booked (Real SAP)'), (string) $row['year'], $row['booked'])
                    ->addSeriesPoint(__('Committed'), (string) $row['year'], $row['committed'])
                    ->addSeriesPoint(__('Available'), (string) $row['year'], $row['available']);
            },
            LivewireCharts::multiLineChartModel()
                ->setTitle(__('Annual financial trend'))
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
            'cashFlowChartOptions' => $cashFlow['options'],
            'cashFlowSummary' => $cashFlow['summary'],
            'plannedCashFlowChartOptions' => $cashFlow['planned_options'],
            'plannedCashFlowSummary' => $cashFlow['planned_summary'],
            'projectionChartOptions' => $cashFlow['projection_options'],
            'projectionRows' => $cashFlow['projection']['rows'],
            'projectionSummaries' => $cashFlow['projection']['summaries'],
            'availableChart' => $availableChart,
            'companies' => $companies,
            'years' => $years,
            'stateOptions' => $this->reportableStateOptions(),
            'investmentOptions' => InvestmentEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
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
        $assignedColumn = $this->currency === 'dollar' ? 'booked' : 'booked_euros';
        $bookedColumn = $this->currency === 'dollar' ? 'real_value' : 'real_value_euros';
        $executedColumn = $this->currency === 'dollar' ? 'executed_dollars' : 'executed_euros';

        return $this->filteredProjectQuery($permission)
            ->with('company:id,company_name')
            ->withSum('data as original_budget', $budgetColumn)
            ->withSum('data as assigned', $assignedColumn)
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
                $assigned = round((float) $projects->sum('assigned'), 2);
                $booked = round((float) $projects->sum('booked'), 2);

                return [
                    'year' => $year,
                    'project_count' => $projects->count(),
                    'budgeted' => $original,
                    'approved' => $approved,
                    'assigned' => $assigned,
                    'booked' => $booked,
                    'committed' => round($assigned - $booked, 2),
                    'executed' => $executed,
                    'available' => round($approved - $assigned, 2),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function coverageRowsByApprovalYear(ProjectPermissionEnum $permission): Collection
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $assignedColumn = $this->currency === 'dollar' ? 'booked' : 'booked_euros';
        $bookedColumn = $this->currency === 'dollar' ? 'real_value' : 'real_value_euros';
        $executedColumn = $this->currency === 'dollar' ? 'executed_dollars' : 'executed_euros';

        return $this->filteredProjectQuery($permission)
            ->withSum('data as original_budget', $budgetColumn)
            ->withSum('data as assigned', $assignedColumn)
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
                    'assigned' => round((float) $projects->sum('assigned'), 2),
                    'booked' => round((float) $projects->sum('booked'), 2),
                    'committed' => round((float) $projects->sum('assigned') - (float) $projects->sum('booked'), 2),
                    'available' => round($approved - (float) $projects->sum('assigned'), 2),
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
            )
            ->when(
                $this->justificationFilter !== [],
                fn (Builder $query) => $query->whereIn('justification', $this->justificationFilter)
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
            || $this->justificationFilter !== []
            || $this->currency !== 'euro';
    }

    private function activeFilterLabels(): array
    {
        return [
            __('activity_control.search') => trim($this->search) ?: __('activity_control.all'),
            __('Plants') => $this->plantFilter === [] ? __('activity_control.all') : implode(', ', $this->plantFilter),
            __('Years') => $this->yearFilter === [] ? __('activity_control.all') : implode(', ', $this->yearFilter),
            __('States') => $this->stateFilter === [] ? __('activity_control.all') : implode(', ', array_map(fn ($value) => __($value), $this->stateFilter)),
            __('Investments') => $this->investmentFilter === [] ? __('activity_control.all') : implode(', ', array_map(fn ($value) => __($value), $this->investmentFilter)),
            __('Classifications') => $this->classificationFilter === [] ? __('activity_control.all') : implode(', ', array_map(fn ($value) => __($value), $this->classificationFilter)),
            __('Justifications') => $this->justificationFilter === [] ? __('activity_control.all') : implode(', ', array_map(fn ($value) => __($value), $this->justificationFilter)),
            __('Currency') => $this->currency === 'dollar' ? 'USD' : 'EUR',
        ];
    }

    private function moneyChartConfig(): array
    {
        $formatter = ChartValueFormatter::compactMoney(
            $this->currency === 'dollar' ? '$' : "\u{20AC}"
        );

        return [
            'yaxis.labels.formatter' => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private const FINANCIAL_LABELS = [
        'budgeted' => 'Budgeted',
        'approved' => 'Approved',
        'booked' => 'Booked (Real SAP)',
        'committed' => 'Committed',
        'available' => 'Available',
    ];

    private function financialSeries(Collection $rows): array
    {
        return collect(self::FINANCIAL_LABELS)->map(
            fn (string $label, string $field): array => [
                'name' => __($label),
                'data' => $rows->pluck($field)->values()->all(),
            ]
        )->values()->all();
    }

    private function financialAxis(Collection $rows): array
    {
        $values = $rows->flatMap(fn (array $row): array => array_values(array_intersect_key($row, self::FINANCIAL_LABELS)));
        $minimum = min(0, (float) $values->min());

        return [
            'min' => $minimum < 0 ? -$this->niceAxisMaximum(abs($minimum)) : 0,
            'max' => $this->niceAxisMaximum(max(0, (float) $values->max())),
            'tickAmount' => 5,
            'forceNiceScale' => false,
        ];
    }

    private function projectsChartOptions(Collection $rows): array
    {
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $moneyFormatter = ChartValueFormatter::compactMoney($symbol);
        $financialAxisMaximum = $this->niceAxisMaximum(max(
            (float) $rows->max('budgeted'),
            (float) $rows->max('assigned'),
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
                ['name' => __('Projects'), 'type' => 'column', 'data' => $rows->pluck('project_count')->values()->all()],
                ['name' => __('Budgeted'), 'type' => 'line', 'data' => $rows->pluck('budgeted')->values()->all()],
                ['name' => __('Assigned'), 'type' => 'line', 'data' => $rows->pluck('assigned')->values()->all()],
                ['name' => __('Executed'), 'type' => 'line', 'data' => $rows->pluck('executed')->values()->all()],
            ],
            'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#4F46E5', '#0EA5E9', '#F59E0B', '#009E0B'],
            'stroke' => ['width' => [0, 4, 4, 4], 'curve' => 'smooth'],
            'plotOptions' => ['bar' => ['columnWidth' => '48%', 'borderRadius' => 4]],
            'dataLabels' => ['enabled' => true, 'enabledOnSeries' => [0]],
            'xaxis' => ['categories' => $rows->pluck('year')->map(fn ($year) => (string) $year)->values()->all()],
            'yaxis' => [
                [
                    'seriesName' => __('Projects'),
                    'title' => ['text' => __('Projects')],
                    'decimalsInFloat' => 0,
                    'min' => 0,
                    'max' => $projectsAxisMaximum,
                    'tickAmount' => min(5, $projectsAxisMaximum),
                    'forceNiceScale' => false,
                ],
                [
                    ...$financialAxis,
                    'seriesName' => __('Budgeted'),
                    'opposite' => true,
                    'title' => ['text' => __('Financial value (:value1)', ['value1' => $symbol])],
                    'labels' => [
                        'formatter' => $moneyFormatter,
                        'minWidth' => 70,
                        'maxWidth' => 110,
                    ],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => __('Assigned'),
                    'opposite' => true,
                    'show' => false,
                    'labels' => ['show' => false],
                ],
                [
                    ...$financialAxis,
                    'seriesName' => __('Executed'),
                    'opposite' => true,
                    'show' => false,
                    'labels' => ['show' => false],
                ],
            ],
            'tooltip' => ['y' => ['formatter' => "function(value, context) { if (context.seriesIndex === 0) return Number(value).toLocaleString(document.documentElement.lang) + ' ' + ".json_encode(__('Projects'), JSON_HEX_APOS | JSON_HEX_QUOT)."; return ({$moneyFormatter})(value); }"]],
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

    private function cashFlowChartData(ProjectPermissionEnum $permission): array
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $formatter = ChartValueFormatter::compactMoney($symbol);

        $milestoneValues = $this->filteredProjectQuery($permission)
            ->withSum('data as milestone_budget', $budgetColumn)
            ->with(['projectMilestones:id,project_id,cycle_year,month,percentage,executed_at'])
            ->whereHas('projectMilestones')
            ->get()
            ->flatMap(function (Project $project): Collection {
                $budget = (float) $project->milestone_budget;

                return $project->projectMilestones->map(fn ($milestone): array => [
                    'period' => sprintf('%04d-%02d', $milestone->cycle_year, $milestone->month),
                    'value' => $budget * ((float) $milestone->percentage / 100),
                    'pending' => $milestone->executed_at === null,
                ]);
            });
        $monthlyValues = $milestoneValues
            ->groupBy('period')
            ->map(fn (Collection $items): float => round((float) $items->sum('value'), 2))
            ->sortKeys();

        $selectedYears = array_values(array_unique(array_map('intval', $this->yearFilter)));
        $visibleValues = $selectedYears === [] ? $monthlyValues : $monthlyValues->filter(
            fn (float $value, string $period): bool => in_array((int) substr($period, 0, 4), $selectedYears, true)
        );
        $visibleTotal = round((float) $visibleValues->sum(), 2);
        $outsideValues = $monthlyValues->diffKeys($visibleValues);
        $outsideTotal = round((float) $outsideValues->sum(), 2);
        $displayYears = $selectedYears !== [] ? $selectedYears : $monthlyValues->keys()
            ->map(fn (string $period): int => (int) substr($period, 0, 4))->unique()->values()->all();
        sort($displayYears);
        $periods = collect();
        foreach ($displayYears as $year) {
            foreach (range(1, 12) as $month) {
                $periods->put(sprintf('%04d-%02d', $year, $month), 0.0);
            }
        }
        $visibleValues = $periods->replace($visibleValues)->sortKeys();
        $categories = $visibleValues->keys()
            ->map(fn (string $period): string => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'))
            ->values()
            ->all();

        $currentMonth = CarbonImmutable::now()->format('Y-m');
        $options = [
            'series' => [['name' => __('Milestone cash flow'), 'data' => $visibleValues->values()->all()]],
            'chart' => ['type' => 'bar', 'height' => 300, 'toolbar' => ['show' => false]],
            'colors' => $visibleValues->keys()->map(
                fn (string $period): string => $period < $currentMonth ? '#F97316' : '#7DD3FC'
            )->values()->all(),
            'stroke' => ['width' => 0],
            'fill' => ['opacity' => 1],
            'plotOptions' => ['bar' => ['columnWidth' => '58%', 'borderRadius' => 5, 'distributed' => true]],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $categories,
                'title' => ['text' => __('Milestone month')],
                'labels' => ['rotate' => -45, 'hideOverlappingLabels' => true],
            ],
            'yaxis' => [
                'min' => 0,
                'forceNiceScale' => true,
                'title' => ['text' => __('Cash flow (:value1)', ['value1' => $symbol])],
                'labels' => ['formatter' => $formatter, 'minWidth' => 80, 'maxWidth' => 120],
            ],
            'tooltip' => ['y' => ['formatter' => $formatter]],
            'legend' => ['show' => false],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
        $realColumn = $this->currency === 'dollar' ? 'real_value' : 'real_value_euros';
        $realRows = \App\Models\Data::query()
            ->whereIn('project_id', $this->filteredProjectQuery($permission)->select('projects.id'))
            ->get(['document_date', 'accounting_date', $realColumn]);
        $comparison = $this->plannedExecutionComparison($realRows, $realColumn, $monthlyValues, $options);
        $documentValues = $this->monthlyRealValues($realRows, 'document_date', $realColumn);
        $options['series'] = [
            ['name' => __('cash_flow_projection.planned'), 'data' => $visibleValues->values()->all()],
            ['name' => __('cash_flow_projection.actual'), 'data' => $visibleValues->keys()->map(fn (string $period): float => (float) $documentValues->get($period, 0.0))->all()],
        ];
        $options['colors'] = ['#94A3B8', '#38BDF8'];
        $options['chart']['stacked'] = false;
        $options['plotOptions']['bar']['distributed'] = false;
        $options['legend'] = ['show' => true, 'position' => 'top'];
        $options['tooltip']['shared'] = true;
        $options['tooltip']['intersect'] = false;
        $options['tooltip']['hideEmptySeries'] = false;
        $options['tooltip']['x'] = ['show' => true];
        unset($options['yaxis']['min']); // Real values may contain negative adjustments.

        $projection = app(\App\Services\Resume\CashFlowForecast::class)->annualProjection($monthlyValues, $documentValues, $periods);
        $projectionOptions = $options;
        $projectionOptions['series'] = [
            ['name' => __('cash_flow_projection.planned'), 'data' => array_column($projection['rows'], 'planned')],
            ['name' => __('cash_flow_projection.actual'), 'data' => array_column($projection['rows'], 'actual')],
            ['name' => __('cash_flow_projection.projected'), 'data' => array_column($projection['rows'], 'projected')],
        ];
        $projectionOptions['colors'] = ['#94A3B8', '#38BDF8', '#8B5CF6'];
        $projectionOptions['xaxis']['categories'] = array_column($projection['rows'], 'month');
        $encodedSymbol = json_encode($symbol, JSON_THROW_ON_ERROR);
        $projectionOptions['tooltip']['y']['formatter'] = "function(value) { return value == null ? '—' : {$encodedSymbol} + ' ' + Number(value).toLocaleString(document.documentElement.lang, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }";

        return [
            'options' => $options,
            'projection_options' => $projectionOptions,
            'projection' => $projection,
            'planned_options' => $comparison['options'],
            'summary' => [
                'years' => implode(', ', $displayYears),
                'total' => $visibleTotal,
                'outside_total' => $outsideTotal,
                'outside_years' => $outsideValues->keys()->map(fn (string $period): string => substr($period, 0, 4))
                    ->unique()->values()->implode(', '),
            ],
            'planned_summary' => $comparison['summary'],
        ];
    }

    private function monthlyRealValues(Collection $rows, string $dateColumn, string $valueColumn): Collection
    {
        return $rows->filter(fn ($row) => filled($row->{$dateColumn}))
            ->groupBy(fn ($row) => substr($row->{$dateColumn}, 0, 7))
            ->map(fn (Collection $items): float => round((float) $items->sum($valueColumn), 2));
    }

    private function plannedExecutionComparison(Collection $rows, string $column, Collection $planned, array $options): array
    {
        $actual = $this->monthlyRealValues($rows, 'accounting_date', $column);
        $years = $this->yearFilter !== [] ? array_map('intval', $this->yearFilter)
            : $planned->keys()->merge($actual->keys())->map(fn ($period) => (int) substr($period, 0, 4))->unique()->all();
        sort($years);
        $periods = collect();
        foreach ($years as $year) {
            foreach (range(1, 12) as $month) {
                $periods->put(sprintf('%04d-%02d', $year, $month), 0.0);
            }
        }
        $plannedVisible = $periods->replace($planned->intersectByKeys($periods));
        $actualVisible = $periods->replace($actual->intersectByKeys($periods));
        $currentMonth = CarbonImmutable::now()->format('Y-m');
        $categories = $periods->keys()->map(fn ($period) => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'))->all();
        $options['series'] = [
            ['name' => __('Planned milestones'), 'data' => $plannedVisible->map(fn ($value, $period) => [
                'x' => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'),
                'y' => $value, 'fillColor' => $period < $currentMonth ? '#F97316' : '#7DD3FC',
            ])->values()->all()],
            ['name' => __('Real SAP'), 'data' => $actualVisible->map(fn ($value, $period) => [
                'x' => CarbonImmutable::createFromFormat('!Y-m', $period)->translatedFormat('M Y'),
                'y' => $value, 'fillColor' => '#94A3B8',
            ])->values()->all()],
        ];
        $options['colors'] = ['#7DD3FC', '#94A3B8'];
        $options['chart']['stacked'] = false;
        $options['plotOptions']['bar']['distributed'] = false;
        $options['xaxis']['categories'] = $categories;
        $options['xaxis']['title']['text'] = __('Month');
        unset($options['yaxis']['min']); // Preserve negative accounting adjustments.
        $options['legend'] = ['show' => true, 'position' => 'top'];
        $options['tooltip']['shared'] = true;
        $options['tooltip']['intersect'] = false;

        return ['options' => $options, 'summary' => [
            'forecast_rows' => app(\App\Services\Resume\CashFlowForecast::class)->rows($planned, $actual, $periods),
            'years' => implode(', ', $years),
            'total' => round((float) $plannedVisible->sum(), 2),
            'actual_total' => round((float) $actualVisible->sum(), 2),
            'outside_total' => round((float) $planned->diffKeys($periods)->sum(), 2),
            'outside_actual' => round((float) $actual->diffKeys($periods)->sum(), 2),
            'undated_total' => round((float) $rows->filter(fn ($row) => blank($row->accounting_date))->sum($column), 2),
        ]];
    }

    private function comparisonChartOptions(Collection $rows): array
    {
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $formatter = ChartValueFormatter::compactMoney($symbol);

        return [
            'series' => $this->financialSeries($rows),
            'chart' => ['type' => 'area', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#2563EB', '#8B5CF6', '#059669', '#F59E0B', '#0891B2'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'fill' => [
                'type' => 'gradient',
                'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.35, 'opacityTo' => 0.04],
            ],
            'dataLabels' => ['enabled' => false],
            'xaxis' => [
                'categories' => $rows->pluck('year')->map(fn ($year) => (string) $year)->values()->all(),
            ],
            'yaxis' => [
                ...$this->financialAxis($rows),
                'title' => ['text' => __('Financial value (:value1)', ['value1' => $symbol])],
                'labels' => ['formatter' => $formatter, 'minWidth' => 80, 'maxWidth' => 130],
            ],
            'tooltip' => ['y' => ['formatter' => $formatter]],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }
}
