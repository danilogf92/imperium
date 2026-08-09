<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Data;
use App\Models\Project;
use App\Support\DashboardCache;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\RadarChartModel;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    /** @var array<int, string> */
    public array $yearSearch = [];

    /** @var array<int, string> */
    public array $stateSearch = [];

    /** @var array<int, string> */
    public array $typeOfProjectSearch = [];

    /** @var array<int, string> */
    public array $investmentSearch = [];

    /** @var array<int, string> */
    public array $justificationSearch = [];

    /** @var array<int, string> */
    public array $companyFilter = [];

    /** @var array<int, string> */
    public array $years = [];

    public string $currency = 'euro';

    private const CACHE_TTL_MINUTES = 60;

    private const MONTHS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];

    private const INVESTMENT_COLORS = [
        'Innovation' => '#f59e0b',
        'Efficiency & Saving' => '#ef4444',
        'Replacement & Restructuring' => '#3b82f6',
        'Quality & Hygiene' => '#22c55e',
        'Health & Safety' => '#eab308',
        'Environment' => '#14b8a6',
        'Maintenance' => '#0ea5e9',
        'Capacity Increase' => '#8b5cf6',
    ];

    private const STATE_COLORS = [
        'Capex' => '#6366f1',
        'Planning' => '#f59e0b',
        'Execution' => '#0ea5e9',
        'Finished' => '#22c55e',
    ];

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);

        $this->loadYears();
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

    public function render(): View
    {
        $this->sanitizeFilters();

        $statistics = Cache::remember(
            $this->cacheKey(),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => $this->loadStatistics()
        );

        $projectsByInvestment = collect($statistics['projectsByInvestment'])->map(fn (array $row) => (object) $row);
        $projectsByState = collect($statistics['projectsByState'])->map(fn (array $row) => (object) $row);
        $budgetByInvestment = collect($statistics['budgetByInvestment'])->map(fn (array $row) => (object) $row);
        $budgetByState = collect($statistics['budgetByState'])->map(fn (array $row) => (object) $row);
        $budgetByArea = collect($statistics['budgetByArea'])->map(fn (array $row) => (object) $row);
        $projectsByCreationMonth = collect($statistics['projectsByCreationMonth'])->map(fn (array $row) => (object) $row);
        $budgetByCreationMonth = collect($statistics['budgetByCreationMonth'])->map(fn (array $row) => (object) $row);
        $realValueByStartMonth = collect($statistics['realValueByStartMonth']);
        $realValueByApprovalMonth = collect($statistics['realValueByApprovalMonth']);
        $forecastEndDatesByMonth = collect($statistics['forecastEndDatesByMonth']);
        $closeDatesByMonth = collect($statistics['closeDatesByMonth']);

        $projectCount = (int) $statistics['projectCount'];
        $hasProjects = $projectCount > 0;
        $hasFinancialData = $budgetByInvestment->isNotEmpty();

        return view('livewire.dashboard.dashboard', [
            'companies' => auth()->user()?->availableCompanies() ?? collect(),
            'stateOptions' => ProjectStateEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'projectCount' => $projectCount,
            'projectsWithData' => (int) $statistics['projectsWithData'],
            'hasProjects' => $hasProjects,
            'budgeted' => (float) $statistics['budgeted'],
            'booked' => (float) $statistics['booked'],
            'executed' => (float) $statistics['executed'],
            'realValue' => (float) $statistics['realValue'],
            'projectsByInvestmentChart' => $this->projectsByInvestmentChart($projectsByInvestment),
            'projectsByStateChart' => $this->projectsByStateChart($projectsByState),
            'projectsByStateColumnChart' => $this->projectsByStateColumnChart($projectsByState),
            'budgetByInvestmentChart' => $this->budgetColumnChart($budgetByInvestment),
            'budgetByStateChart' => $this->budgetStateChart($budgetByState),
            'budgetByStateColumnChart' => $this->budgetStateColumnChart($budgetByState),
            'budgetByInvestmentRadarChart' => $this->budgetInvestmentRadarChart($budgetByInvestment),
            'budgetByAreaRadarChart' => $this->budgetAreaRadarChart($budgetByArea),
            'projectsCreationCurveChart' => $this->cumulativeLineChart(
                $projectsByCreationMonth,
                'Cumulative projects by creation month'
            ),
            'budgetCreationCurveChart' => $this->cumulativeLineChart(
                $budgetByCreationMonth,
                'Cumulative budget by project creation month',
                true
            ),
            'plannedVsActualExecutionChart' => $hasProjects
                ? $this->plannedVsActualExecutionChart(
                    $realValueByStartMonth,
                    $realValueByApprovalMonth,
                    (float) $statistics['budgeted']
                )
                : null,
            'forecastVsCloseDateChart' => $hasProjects
                ? $this->forecastVsCloseDateChart(
                    $forecastEndDatesByMonth,
                    $closeDatesByMonth
                )
                : null,
            'scheduleRealValueTotal' => (float) $statistics['budgeted'],
            'hasFinancialData' => $hasFinancialData,
            'hasAreaData' => $budgetByArea->isNotEmpty(),
        ])->layout('layouts.app');
    }

    private function loadStatistics(): array
    {
        $columns = $this->currencyColumns();

        $projectCount = $this->projectQuery()->count();

        $projectsByInvestment = $this->projectQuery()
            ->selectRaw('investments AS label, COUNT(*) AS total')
            ->groupBy('investments')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'total' => (int) $row->total])
            ->all();

        $projectsByState = $this->projectQuery()
            ->selectRaw('state AS label, COUNT(*) AS total')
            ->groupBy('state')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'total' => (int) $row->total])
            ->all();

        $financialTotals = $this->dataQuery()
            ->selectRaw(
                'COUNT(DISTINCT data.project_id) AS projects_with_data, '.
                    "COALESCE(SUM(data.{$columns['budgeted']}), 0) AS budgeted, ".
                    "COALESCE(SUM(data.{$columns['booked']}), 0) AS booked, ".
                    "COALESCE(SUM(data.{$columns['executed']}), 0) AS executed, ".
                    "COALESCE(SUM(data.{$columns['real_value']}), 0) AS real_value"
            )
            ->first();

        $budgetByInvestment = $this->dataQuery()
            ->selectRaw("projects.investments AS label, COALESCE(SUM(data.{$columns['budgeted']}), 0) AS total")
            ->groupBy('projects.investments')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'total' => (float) $row->total])
            ->all();

        $budgetByState = $this->dataQuery()
            ->selectRaw("projects.state AS label, COALESCE(SUM(data.{$columns['budgeted']}), 0) AS total")
            ->groupBy('projects.state')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'total' => (float) $row->total])
            ->all();

        $budgetByArea = $this->dataQuery()
            ->whereNotNull('data.area')
            ->where('data.area', '<>', '')
            ->selectRaw("data.area AS label, COALESCE(SUM(data.{$columns['budgeted']}), 0) AS total")
            ->groupBy('data.area')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'total' => (float) $row->total])
            ->all();

        $projectsByCreationMonth = $this->projectQuery()
            ->whereNotNull('projects.created_at')
            ->selectRaw($this->monthExpression('projects.created_at').' AS month, COUNT(*) AS total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => (string) $row->month, 'total' => (int) $row->total])
            ->all();

        $budgetByCreationMonth = $this->dataQuery()
            ->whereNotNull('projects.created_at')
            ->selectRaw(
                $this->monthExpression('projects.created_at').
                    " AS month, COALESCE(SUM(data.{$columns['budgeted']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => ['month' => (string) $row->month, 'total' => (float) $row->total])
            ->all();

        $realValueByStartMonth = $this->dataQuery()
            ->whereNotNull('projects.forecast_start_date')
            ->selectRaw(
                $this->monthNumberExpression('projects.forecast_start_date').
                    " AS month, COALESCE(SUM(data.{$columns['real_value']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (float) $value)
            ->all();

        $realValueByApprovalMonth = $this->dataQuery()
            ->whereNotNull('projects.approve_date')
            ->selectRaw(
                $this->monthNumberExpression('projects.approve_date').
                    " AS month, COALESCE(SUM(data.{$columns['real_value']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (float) $value)
            ->all();

        $forecastEndDatesByMonth = $this->projectQuery()
            ->whereNotNull('projects.forecast_end_date')
            ->selectRaw($this->monthNumberExpression('projects.forecast_end_date').' AS month, COUNT(*) AS total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (int) $value)
            ->all();

        $closeDatesByMonth = $this->projectQuery()
            ->whereNotNull('projects.close_date')
            ->selectRaw($this->monthNumberExpression('projects.close_date').' AS month, COUNT(*) AS total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value) => (int) $value)
            ->all();

        return [
            'projectCount' => $projectCount,
            'projectsWithData' => (int) ($financialTotals->projects_with_data ?? 0),
            'budgeted' => (float) ($financialTotals->budgeted ?? 0),
            'booked' => (float) ($financialTotals->booked ?? 0),
            'executed' => (float) ($financialTotals->executed ?? 0),
            'realValue' => (float) ($financialTotals->real_value ?? 0),
            'projectsByInvestment' => $projectsByInvestment,
            'projectsByState' => $projectsByState,
            'budgetByInvestment' => $budgetByInvestment,
            'budgetByState' => $budgetByState,
            'budgetByArea' => $budgetByArea,
            'projectsByCreationMonth' => $projectsByCreationMonth,
            'budgetByCreationMonth' => $budgetByCreationMonth,
            'realValueByStartMonth' => $realValueByStartMonth,
            'realValueByApprovalMonth' => $realValueByApprovalMonth,
            'forecastEndDatesByMonth' => $forecastEndDatesByMonth,
            'closeDatesByMonth' => $closeDatesByMonth,
        ];
    }

    private function projectQuery(): Builder
    {
        return $this->applyProjectFilters(Project::query(), 'projects');
    }

    private function dataQuery(): Builder
    {
        $query = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id');

        return $this->applyProjectFilters($query, 'projects');
    }

    private function applyProjectFilters(Builder $query, string $table): Builder
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $query->whereIn(
            "{$table}.company_id",
            $user->availableCompaniesQuery()
                ->select('companies.id')
                ->reorder()
        );

        if ($this->companyFilter !== []) {
            $query->whereExists(function ($companyQuery) use ($table): void {
                $companyQuery
                    ->selectRaw('1')
                    ->from('companies')
                    ->whereColumn('companies.id', "{$table}.company_id")
                    ->whereIn('companies.company_code', $this->companyFilter);
            });
        }

        if ($this->yearSearch !== []) {
            $query->where(function (Builder $yearQuery) use ($table): void {
                foreach ($this->yearSearch as $year) {
                    $yearQuery->orWhereBetween("{$table}.forecast_start_date", [
                        "{$year}-01-01 00:00:00",
                        "{$year}-12-31 23:59:59",
                    ]);
                }
            });
        }

        return $query
            ->when($this->stateSearch !== [], fn (Builder $q) => $q->whereIn("{$table}.state", $this->stateSearch))
            ->when($this->typeOfProjectSearch !== [], fn (Builder $q) => $q->whereIn("{$table}.classification_of_investments", $this->typeOfProjectSearch))
            ->when($this->investmentSearch !== [], fn (Builder $q) => $q->whereIn("{$table}.investments", $this->investmentSearch))
            ->when($this->justificationSearch !== [], fn (Builder $q) => $q->whereIn("{$table}.justification", $this->justificationSearch));
    }

    private function loadYears(): void
    {
        $query = $this->projectQuery()->whereNotNull('projects.forecast_start_date');

        $this->years = DB::connection()->getDriverName() === 'sqlite'
            ? $query
                ->selectRaw("strftime('%Y', projects.forecast_start_date) AS year")
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year): string => (string) $year)
                ->all()
            : $query
                ->selectRaw('YEAR(projects.forecast_start_date) AS year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year): string => (string) $year)
                ->all();
    }

    private function sanitizeFilters(): void
    {
        $allowedCompanies = auth()->user()?->availableCompanyCodes() ?? [];

        $this->companyFilter = $this->normalizeSelection($this->companyFilter, $allowedCompanies);
        $this->yearSearch = $this->normalizeSelection($this->yearSearch, $this->years);
        $this->stateSearch = $this->normalizeSelection($this->stateSearch, array_column(ProjectStateEnum::cases(), 'value'));
        $this->typeOfProjectSearch = $this->normalizeSelection($this->typeOfProjectSearch, array_column(InvestmentClassificationEnum::cases(), 'value'));
        $this->investmentSearch = $this->normalizeSelection($this->investmentSearch, array_column(InvestmentEnum::cases(), 'value'));
        $this->justificationSearch = $this->normalizeSelection($this->justificationSearch, array_column(ProjectJustificationEnum::cases(), 'value'));

        if (! in_array($this->currency, ['euro', 'dollar'], true)) {
            $this->currency = 'euro';
        }
    }

    /** @param array<int, string> $values @param array<int, string> $allowed */
    private function normalizeSelection(array $values, array $allowed): array
    {
        $normalized = array_values(array_unique(array_intersect($values, $allowed)));
        sort($normalized);

        return $normalized;
    }

    private function cacheKey(): string
    {
        return 'dashboard:v'.DashboardCache::version().':'.hash('sha256', json_encode([
            'user' => auth()->id(),
            'companies' => $this->companyFilter,
            'years' => $this->yearSearch,
            'states' => $this->stateSearch,
            'classifications' => $this->typeOfProjectSearch,
            'investments' => $this->investmentSearch,
            'justifications' => $this->justificationSearch,
            'currency' => $this->currency,
        ], JSON_THROW_ON_ERROR));
    }

    /** @return array{budgeted: string, booked: string, executed: string, real_value: string} */
    private function currencyColumns(): array
    {
        return $this->currency === 'dollar'
            ? ['budgeted' => 'global_price', 'booked' => 'booked', 'executed' => 'executed_dollars', 'real_value' => 'real_value']
            : ['budgeted' => 'global_price_euros', 'booked' => 'booked_euros', 'executed' => 'executed_euros', 'real_value' => 'real_value_euros'];
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function monthNumberExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    private function projectsByInvestmentChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)->setTitle('Projects by investment')->setAnimated(true)->setHorizontal(true)->withDataLabels()->withGrid();

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn($label, (int) $value->total, self::INVESTMENT_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function projectsByStateChart(Collection $values): PieChartModel
    {
        $chart = (new PieChartModel)
            ->setTitle('Projects by state')->setAnimated(true)->setType('donut')->withDataLabels()->withLegend()
            ->setJsonConfig(['dataLabels.formatter' => $this->percentFormatter(), 'tooltip.y.formatter' => $this->projectsFormatter()]);

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addSlice($label, (int) $value->total, self::STATE_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function projectsByStateColumnChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)->setTitle('Project status #')->setAnimated(true)->withDataLabels()->withGrid();

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn($label, (int) $value->total, self::STATE_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function budgetColumnChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)->setTitle('Budget by investment')->setAnimated(true)->setHorizontal(true)->withDataLabels()->withGrid()->setJsonConfig($this->moneyChartConfig('xaxis'));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn($label, round((float) $value->total, 2), self::INVESTMENT_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function budgetStateChart(Collection $values): PieChartModel
    {
        $chart = (new PieChartModel)->setTitle('Budget by state')->setAnimated(true)->setType('donut')->withDataLabels()->withLegend()->setJsonConfig(['dataLabels.formatter' => $this->percentFormatter(), 'tooltip.y.formatter' => $this->moneyFormatter()]);

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addSlice($label, round((float) $value->total, 2), self::STATE_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function budgetStateColumnChart(Collection $values): ColumnChartModel
    {
        $chart = (new ColumnChartModel)->setTitle('Project status value')->setAnimated(true)->withDataLabels()->withGrid()->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn($label, round((float) $value->total, 2), self::STATE_COLORS[$label] ?? '#64748b');
        }

        return $chart;
    }

    private function budgetInvestmentRadarChart(Collection $values): RadarChartModel
    {
        $chart = (new RadarChartModel)->setTitle('Type of Investment')->setAnimated(true)->setJsonConfig($this->moneyChartConfig('yaxis'));
        foreach ($values as $value) {
            $chart->addSeries('Investment', (string) $value->label, round((float) $value->total, 2));
        }

        return $chart;
    }

    private function budgetAreaRadarChart(Collection $values): RadarChartModel
    {
        $chart = (new RadarChartModel)->setTitle('Area Classification')->setAnimated(true)->setJsonConfig($this->moneyChartConfig('yaxis'));
        foreach ($values as $value) {
            $chart->addSeries('Investment', (string) $value->label, round((float) $value->total, 2));
        }

        return $chart;
    }

    private function cumulativeLineChart(Collection $values, string $title, bool $money = false): LineChartModel
    {
        $chart = (new LineChartModel)->setTitle($title)->setAnimated(true)->singleLine()->withDataLabels()->withGrid()->setColors(['#4f46e5']);
        if ($money) {
            $chart->setJsonConfig($this->moneyChartConfig('yaxis'));
        }

        $cumulative = 0.0;
        foreach ($values as $value) {
            $cumulative += (float) $value->total;
            $chart->addPoint(Carbon::createFromFormat('Y-m', (string) $value->month)->translatedFormat('M Y'), round($cumulative, 2));
        }

        return $chart;
    }

    private function plannedVsActualExecutionChart(Collection $plannedValues, Collection $actualValues, float $total): LineChartModel
    {
        $chart = (new LineChartModel)->setTitle('StartDate vs Approved Date')->setAnimated(true)->multiLine()->setStraightCurve()->setStrokeWidth(3)->withGrid()->withLegend()->setColors(['#2563eb', '#ef4444'])->setJsonConfig([
            'yaxis.min' => 0,
            'yaxis.max' => 100,
            'yaxis.tickAmount' => 5,
            'yaxis.labels.formatter' => '(value) => `${Math.round(value)}%`',
            'tooltip.y.formatter' => '(value) => `${Number(value).toFixed(1)}%`',
            'markers.size' => 4,
        ]);

        $planned = 0.0;
        $actual = 0.0;
        foreach (self::MONTHS as $number => $month) {
            $planned += (float) $plannedValues->get($number, 0);
            $actual += (float) $actualValues->get($number, 0);
            $chart
                ->addSeriesPoint('Planned % (Start date)', $month, $total > 0 ? round(($planned / $total) * 100, 2) : 0)
                ->addSeriesPoint('Actual % (Approved date)', $month, $total > 0 ? round(($actual / $total) * 100, 2) : 0);
        }

        return $chart;
    }

    private function forecastVsCloseDateChart(Collection $forecastValues, Collection $closeValues): LineChartModel
    {
        $chart = (new LineChartModel)->setTitle('Forecast EndDate vs CloseDate')->setAnimated(true)->multiLine()->setStraightCurve()->setStrokeWidth(3)->withGrid()->withLegend()->setColors(['#2563eb', '#16a34a'])->setJsonConfig([
            'yaxis.min' => 0,
            'yaxis.forceNiceScale' => true,
            'yaxis.labels.formatter' => '(value) => Math.round(value)',
            'tooltip.y.formatter' => '(value) => `${Math.round(value)} projects`',
            'markers.size' => 4,
        ]);

        foreach (self::MONTHS as $number => $month) {
            $chart
                ->addSeriesPoint('Forecast EndDate', $month, (int) $forecastValues->get($number, 0))
                ->addSeriesPoint('CloseDate', $month, (int) $closeValues->get($number, 0));
        }

        return $chart;
    }

    /** @return array<string, string> */
    private function moneyChartConfig(string $axis): array
    {
        $formatter = $this->moneyFormatter();

        return ["{$axis}.labels.formatter" => $formatter, 'dataLabels.formatter' => $formatter, 'tooltip.y.formatter' => $formatter];
    }

    private function moneyFormatter(): string
    {
        $symbol = json_encode($this->currency === 'dollar' ? '$' : '€', JSON_THROW_ON_ERROR);

        return "function(value) { return {$symbol} + ' ' + Number(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }";
    }

    private function percentFormatter(): string
    {
        return "function(value) { return Number(value).toFixed(1) + '%'; }";
    }

    private function projectsFormatter(): string
    {
        return "function(value) { return Number(value).toLocaleString() + ' projects'; }";
    }
}
