<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Data;
use App\Models\Project;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\RadarChartModel;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    public string $currency = 'euro';


    /** @var array<int, string> */
    public array $companyFilter = [];

    /** @var array<int, string> */
    public array $years = [];

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
        abort_unless(auth()->user(), 403);

        $this->loadYears();
    }

    public function updatedCompanyFilter(): void
    {
        $allowedCodes = auth()->user()?->availableCompanyCodes() ?? [];
        $this->companyFilter = array_values(
            array_intersect($this->companyFilter, $allowedCodes)
        );
    }

    public function updatedYearSearch(): void
    {
        $this->yearSearch = array_values(
            array_intersect($this->yearSearch, $this->years)
        );
    }

    public function resetAll(): void
    {
        $this->reset([
            'yearSearch',
            'stateSearch',
            'typeOfProjectSearch',
            'investmentSearch',
            'justificationSearch',
            'currency',
            'companyFilter',
        ]);
    }

    public function render(): View
    {
        $projectQuery = $this->projectQuery();
        $projectIds = (clone $projectQuery)->select('projects.id');
        $currencyColumns = $this->currencyColumns();

        $totals = Data::query()
            ->whereIn('project_id', $projectIds)
            ->selectRaw(
                "COALESCE(SUM({$currencyColumns['budgeted']}), 0) AS budgeted, "
                    . "COALESCE(SUM({$currencyColumns['booked']}), 0) AS booked, "
                    . "COALESCE(SUM({$currencyColumns['executed']}), 0) AS executed, "
                    . "COALESCE(SUM({$currencyColumns['real_value']}), 0) AS real_value"
            )
            ->first();

        $projectCount = (clone $projectQuery)->count();
        $projectsWithData = (clone $projectQuery)
            ->whereHas('data')
            ->count();

        $projectsByInvestment = (clone $projectQuery)
            ->selectRaw('investments, COUNT(*) AS total')
            ->groupBy('investments')
            ->orderByDesc('total')
            ->get();

        $projectsByState = (clone $projectQuery)
            ->selectRaw('state, COUNT(*) AS total')
            ->groupBy('state')
            ->orderByDesc('total')
            ->get();

        $budgetByInvestment = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereIn('projects.id', (clone $projectQuery)->select('projects.id'))
            ->selectRaw(
                'projects.investments AS label, '
                    . "COALESCE(SUM(data.{$currencyColumns['budgeted']}), 0) AS total"
            )
            ->groupBy('projects.investments')
            ->orderByDesc('total')
            ->get();

        $budgetByState = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereIn('projects.id', (clone $projectQuery)->select('projects.id'))
            ->selectRaw(
                'projects.state AS label, '
                    . "COALESCE(SUM(data.{$currencyColumns['budgeted']}), 0) AS total"
            )
            ->groupBy('projects.state')
            ->orderByDesc('total')
            ->get();

        $budgetByArea = Data::query()
            ->whereIn('project_id', (clone $projectQuery)->select('projects.id'))
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->selectRaw(
                'area AS label, '
                    . "COALESCE(SUM({$currencyColumns['budgeted']}), 0) AS total"
            )
            ->groupBy('area')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $projectsByCreationMonth = (clone $projectQuery)
            ->whereNotNull('created_at')
            ->selectRaw(
                $this->monthExpression('created_at')
                    . ' AS month, COUNT(*) AS total'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $budgetByCreationMonth = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereIn('projects.id', (clone $projectQuery)->select('projects.id'))
            ->whereNotNull('projects.created_at')
            ->selectRaw(
                $this->monthExpression('projects.created_at') . ' AS month, '
                    . "COALESCE(SUM(data.{$currencyColumns['budgeted']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $realValueByStartMonth = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereIn('projects.id', (clone $projectQuery)->select('projects.id'))
            ->whereNotNull('projects.forecast_start_date')
            ->selectRaw(
                $this->monthNumberExpression('projects.forecast_start_date')
                    . " AS month, COALESCE(SUM(data.{$currencyColumns['real_value']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $realValueByApprovalMonth = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->whereIn('projects.id', (clone $projectQuery)->select('projects.id'))
            ->whereNotNull('projects.approve_date')
            ->selectRaw(
                $this->monthNumberExpression('projects.approve_date')
                    . " AS month, COALESCE(SUM(data.{$currencyColumns['real_value']}), 0) AS total"
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $forecastEndDatesByMonth = (clone $projectQuery)
            ->whereNotNull('forecast_end_date')
            ->selectRaw(
                $this->monthNumberExpression('forecast_end_date')
                    . ' AS month, COUNT(*) AS total'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $closeDatesByMonth = (clone $projectQuery)
            ->whereNotNull('close_date')
            ->selectRaw(
                $this->monthNumberExpression('close_date')
                    . ' AS month, COUNT(*) AS total'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        return view('livewire.dashboard.dashboard', [
            'companies' => auth()->user()?->availableCompanies() ?? collect(),
            'stateOptions' => ProjectStateEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'projectCount' => $projectCount,
            'projectsWithData' => $projectsWithData,
            'hasProjects' => $projectCount > 0,
            'budgeted' => (float) $totals->budgeted,
            'booked' => (float) $totals->booked,
            'executed' => (float) $totals->executed,
            'realValue' => (float) $totals->real_value,
            'projectsByInvestmentChart' =>
            $this->projectsByInvestmentChart($projectsByInvestment),
            'projectsByStateChart' =>
            $this->projectsByStateChart($projectsByState),
            'projectsByStateColumnChart' =>
            $this->projectsByStateColumnChart($projectsByState),
            'budgetByInvestmentChart' =>
            $this->budgetColumnChart($budgetByInvestment, 1.0),
            'budgetByStateChart' =>
            $this->budgetStateChart($budgetByState, 1.0),
            'budgetByStateColumnChart' =>
            $this->budgetStateColumnChart($budgetByState, 1.0),
            'budgetByInvestmentRadarChart' =>
            $this->budgetInvestmentRadarChart(
                $budgetByInvestment,
                1.0
            ),
            'budgetByAreaRadarChart' =>
            $this->budgetAreaRadarChart($budgetByArea, 1.0),
            'projectsCreationCurveChart' =>
            $this->cumulativeLineChart(
                $projectsByCreationMonth,
                'Cumulative projects by creation month'
            ),
            'budgetCreationCurveChart' =>
            $this->cumulativeLineChart(
                $budgetByCreationMonth,
                'Cumulative budget by project creation month',
                1.0
            ),
            'plannedVsActualExecutionChart' =>
            $this->plannedVsActualExecutionChart(
                $realValueByStartMonth,
                $realValueByApprovalMonth,
                (float) $totals->real_value
            ),
            'forecastVsCloseDateChart' =>
            $this->forecastVsCloseDateChart(
                $forecastEndDatesByMonth,
                $closeDatesByMonth
            ),
            'scheduleRealValueTotal' =>
            (float) $totals->real_value,
            'hasFinancialData' => $budgetByInvestment->isNotEmpty(),
            'hasAreaData' => $budgetByArea->isNotEmpty(),
        ])->layout('layouts.app');
    }

    private function projectQuery(): Builder
    {
        $user = auth()->user();

        abort_unless($user, 403);

        return Project::query()
            ->whereIn(
                'company_id',
                $user->availableCompaniesQuery()
                    ->select('companies.id')
                    ->reorder()
            )
            ->when(
                $this->companyFilter !== [],
                fn(Builder $query): Builder => $query->whereHas(
                    'company',
                    fn(Builder $companyQuery): Builder => $companyQuery
                        ->whereIn('company_code', $this->companyFilter)
                )
            )
            ->when(
                $this->yearSearch !== [],
                fn(Builder $query): Builder => $query->where(
                    function (Builder $yearQuery): void {
                        foreach ($this->yearSearch as $year) {
                            $yearQuery->orWhereYear('forecast_start_date', $year);
                        }
                    }
                )
            )
            ->when(
                $this->stateSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'state',
                    $this->stateSearch
                )
            )
            ->when(
                $this->typeOfProjectSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'classification_of_investments',
                    $this->typeOfProjectSearch
                )
            )
            ->when(
                $this->investmentSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'investments',
                    $this->investmentSearch
                )
            )
            ->when(
                $this->justificationSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'justification',
                    $this->justificationSearch
                )
            );
    }

    private function loadYears(): void
    {
        $this->years = $this->projectQuery()
            ->whereNotNull('forecast_start_date')
            ->pluck('forecast_start_date')
            ->map(
                fn(mixed $date): string => Carbon::parse($date)->format('Y')
            )
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /** @return array{budgeted: string, booked: string, executed: string, real_value: string} */
    private function currencyColumns(): array
    {
        return $this->currency === 'dollar'
            ? [
                'budgeted' => 'global_price',
                'booked' => 'booked',
                'executed' => 'executed_dollars',
                'real_value' => 'real_value',
            ]
            : [
                'budgeted' => 'global_price_euros',
                'booked' => 'booked_euros',
                'executed' => 'executed_euros',
                'real_value' => 'real_value_euros',
            ];
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

    private function projectsByInvestmentChart(
        Collection $values
    ): ColumnChartModel {
        $chart = (new ColumnChartModel())
            ->setTitle('Projects by investment')
            ->setAnimated(true)
            ->setHorizontal(true)
            ->withDataLabels()
            ->withGrid();

        foreach ($values as $value) {
            $label = $value->investments->value;
            $chart->addColumn(
                $label,
                (int) $value->total,
                self::INVESTMENT_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function projectsByStateChart(Collection $values): PieChartModel
    {
        $chart = (new PieChartModel())
            ->setTitle('Projects by state')
            ->setAnimated(true)
            ->setType('donut')
            ->withDataLabels()
            ->withLegend()
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => $this->projectsFormatter(),
            ]);

        foreach ($values as $value) {
            $label = $value->state->value;
            $chart->addSlice(
                $label,
                (int) $value->total,
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetColumnChart(
        Collection $values,
        float $multiplier
    ): ColumnChartModel {
        $chart = (new ColumnChartModel())
            ->setTitle('Budget by investment')
            ->setAnimated(true)
            ->setHorizontal(true)
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig($this->moneyChartConfig('xaxis'));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                round((float) $value->total * $multiplier, 2),
                self::INVESTMENT_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetStateChart(
        Collection $values,
        float $multiplier
    ): PieChartModel {
        $chart = (new PieChartModel())
            ->setTitle('Budget by state')
            ->setAnimated(true)
            ->setType('donut')
            ->withDataLabels()
            ->withLegend()
            ->setJsonConfig([
                'dataLabels.formatter' => $this->percentFormatter(),
                'tooltip.y.formatter' => $this->moneyFormatter(),
            ]);

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addSlice(
                $label,
                round((float) $value->total * $multiplier, 2),
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function projectsByStateColumnChart(
        Collection $values
    ): ColumnChartModel {
        $chart = (new ColumnChartModel())
            ->setTitle('Project status #')
            ->setAnimated(true)
            ->withDataLabels()
            ->withGrid();

        foreach ($values as $value) {
            $label = $value->state->value;
            $chart->addColumn(
                $label,
                (int) $value->total,
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetStateColumnChart(
        Collection $values,
        float $multiplier
    ): ColumnChartModel {
        $chart = (new ColumnChartModel())
            ->setTitle('Project status value')
            ->setAnimated(true)
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($values as $value) {
            $label = (string) $value->label;
            $chart->addColumn(
                $label,
                round((float) $value->total * $multiplier, 2),
                self::STATE_COLORS[$label] ?? '#64748b'
            );
        }

        return $chart;
    }

    private function budgetInvestmentRadarChart(
        Collection $values,
        float $multiplier
    ): RadarChartModel {
        $chart = (new RadarChartModel())
            ->setTitle('Type of Investment')
            ->setAnimated(true)
            ->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($values as $value) {
            $chart->addSeries(
                'Investment',
                (string) $value->label,
                round((float) $value->total * $multiplier, 2)
            );
        }

        return $chart;
    }

    private function budgetAreaRadarChart(
        Collection $values,
        float $multiplier
    ): RadarChartModel {
        $chart = (new RadarChartModel())
            ->setTitle('Area Classification')
            ->setAnimated(true)
            ->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($values as $value) {
            $chart->addSeries(
                'Investment',
                (string) $value->label,
                round((float) $value->total * $multiplier, 2)
            );
        }

        return $chart;
    }

    private function cumulativeLineChart(
        Collection $values,
        string $title,
        float $multiplier = 1
    ): LineChartModel {
        $chart = (new LineChartModel())
            ->setTitle($title)
            ->setAnimated(true)
            ->singleLine()
            ->withDataLabels()
            ->withGrid()
            ->setColors(['#4f46e5']);

        if ($multiplier !== 1.0 || str_contains(strtolower($title), 'budget')) {
            $chart->setJsonConfig($this->moneyChartConfig('yaxis'));
        }

        $cumulative = 0.0;

        foreach ($values as $value) {
            $cumulative += (float) $value->total * $multiplier;

            $chart->addPoint(
                Carbon::createFromFormat('Y-m', (string) $value->month)
                    ->translatedFormat('M Y'),
                round($cumulative, 2)
            );
        }

        return $chart;
    }

    private function plannedVsActualExecutionChart(
        Collection $realValueByStartMonth,
        Collection $realValueByApprovalMonth,
        float $totalRealValue
    ): LineChartModel {
        $chart = (new LineChartModel())
            ->setTitle('StartDate vs Approved Date')
            ->setAnimated(true)
            ->multiLine()
            ->setStraightCurve()
            ->setStrokeWidth(3)
            ->withGrid()
            ->withLegend()
            ->setColors(['#2563eb', '#ef4444'])
            ->setJsonConfig([
                'yaxis.min' => 0,
                'yaxis.max' => 100,
                'yaxis.tickAmount' => 5,
                'yaxis.labels.formatter' =>
                '(value) => `${Math.round(value)}%`',
                'tooltip.y.formatter' =>
                '(value) => `${Number(value).toFixed(1)}%`',
                'markers.size' => 4,
            ]);

        $months = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        $cumulativePlanned = 0.0;
        $cumulativeApproved = 0.0;

        foreach ($months as $monthNumber => $month) {
            $cumulativePlanned +=
                (float) $realValueByStartMonth->get($monthNumber, 0);
            $cumulativeApproved +=
                (float) $realValueByApprovalMonth->get($monthNumber, 0);

            $planned = $totalRealValue > 0
                ? round(($cumulativePlanned / $totalRealValue) * 100, 2)
                : 0.0;
            $actual = $totalRealValue > 0
                ? round(($cumulativeApproved / $totalRealValue) * 100, 2)
                : 0.0;

            $chart
                ->addSeriesPoint('Previsto % (StartDate)', $month, $planned)
                ->addSeriesPoint('Real % (Approved Date)', $month, $actual);
        }

        return $chart;
    }

    private function forecastVsCloseDateChart(
        Collection $forecastEndDatesByMonth,
        Collection $closeDatesByMonth
    ): LineChartModel {
        $chart = (new LineChartModel())
            ->setTitle('Forecast EndDate vs CloseDate')
            ->setAnimated(true)
            ->multiLine()
            ->setStraightCurve()
            ->setStrokeWidth(3)
            ->withGrid()
            ->withLegend()
            ->setColors(['#2563eb', '#16a34a'])
            ->setJsonConfig([
                'yaxis.min' => 0,
                'yaxis.forceNiceScale' => true,
                'yaxis.labels.formatter' => '(value) => Math.round(value)',
                'tooltip.y.formatter' =>
                '(value) => `${Math.round(value)} projects`',
                'markers.size' => 4,
            ]);

        $months = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        foreach ($months as $monthNumber => $month) {
            $chart
                ->addSeriesPoint(
                    'Forecast EndDate',
                    $month,
                    (int) $forecastEndDatesByMonth->get($monthNumber, 0)
                )
                ->addSeriesPoint(
                    'CloseDate',
                    $month,
                    (int) $closeDatesByMonth->get($monthNumber, 0)
                );
        }

        return $chart;
    }

    /** @return array<string, string> */
    private function moneyChartConfig(string $axis): array
    {
        $formatter = $this->moneyFormatter();

        return [
            "{$axis}.labels.formatter" => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function moneyFormatter(): string
    {
        $symbol = json_encode(
            $this->currency === 'dollar' ? '$' : '€',
            JSON_THROW_ON_ERROR
        );

        return "function(value) { return {$symbol} + ' ' + "
            . "Number(value).toLocaleString(undefined, "
            . "{minimumFractionDigits: 2, maximumFractionDigits: 2}); }";
    }

    private function percentFormatter(): string
    {
        return "function(value) { return Number(value).toFixed(1) + '%'; }";
    }

    private function projectsFormatter(): string
    {
        return "function(value) { return Number(value).toLocaleString() "
            . "+ ' projects'; }";
    }
}
