<?php

namespace App\Livewire\Resume;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Exports\ProjectResumeExport;
use App\Models\Project;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Resume extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'plant', except: [])]
    public array $plantFilter = [];

    #[Url(as: 'year', except: [])]
    public array $yearFilter = [];

    #[Url(as: 'state', except: [])]
    public array $stateFilter = [];

    #[Url(as: 'investment', except: [])]
    public array $investmentFilter = [];

    #[Url(as: 'classification', except: [])]
    public array $classificationFilter = [];

    #[Url(except: 'euro')]
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

        return (new ProjectResumeExport())->download(
            $this->summaryRows(ProjectPermissionEnum::Export),
            $this->activeFilterLabels(),
            $this->currency === 'dollar' ? '$' : "\u{20AC}"
        );
    }

    public function render(): View
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
                    ->addSeriesColumn('Committed', (string) $row['year'], $row['committed'])
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

        return view('livewire.resume.resume', [
            'rows' => $rows,
            'stackedChart' => $stackedChart,
            'comparisonChartOptions' => $comparisonChartOptions,
            'projectsChartOptions' => $projectsChartOptions,
            'availableChart' => $availableChart,
            'companies' => $companies,
            'years' => $years,
            'stateOptions' => ProjectStateEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'canExport' => auth()->user()
                ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                ->exists() ?? false,
            'hasActiveFilters' => $this->hasActiveFilters(),
            'currencySymbol' => $this->currency === 'dollar' ? '$' : "\u{20AC}",
        ])->layout('layouts.app');
    }

    private function summaryRows(ProjectPermissionEnum $permission): Collection
    {
        $budgetColumn = $this->currency === 'dollar' ? 'global_price' : 'global_price_euros';
        $approvedColumn = $this->currency === 'dollar' ? 'real_value' : 'real_value_euros';
        $bookedColumn = $this->currency === 'dollar' ? 'booked' : 'booked_euros';

        return $this->filteredProjectQuery($permission)
            ->with('company:id,company_name')
            ->withSum('data as original_budget', $budgetColumn)
            ->withSum('data as approved', $approvedColumn)
            ->withSum('data as booked', $bookedColumn)
            ->withSum('data as committed_value', 'committed')
            ->whereNotNull('forecast_start_date')
            ->orderBy('forecast_start_date')
            ->get()
            ->groupBy(fn (Project $project): int => (int) $project->forecast_start_date?->year)
            ->map(function (Collection $projects, int $year): array {
                $original = round((float) $projects->sum('original_budget'), 2);
                $approved = round((float) $projects->sum('approved'), 2);
                $booked = round((float) $projects->sum('booked'), 2);
                $committed = round((float) $projects->sum('committed_value'), 2);

                return [
                    'year' => $year,
                    'project_count' => $projects->count(),
                    'budgeted' => $original,
                    'approved' => $approved,
                    'booked' => $booked,
                    'committed' => $committed,
                    'available' => round($approved - $booked - $committed, 2),
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

        return Project::query()->whereIn(
            'company_id',
            $user->companiesForPermissionQuery($permission)
                ->select('companies.id')
                ->reorder()
        );
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
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";
        $formatter = "function(value) { return '{$symbol} ' + Number(value).toLocaleString(undefined, "
            . "{minimumFractionDigits: 0, maximumFractionDigits: 2}); }";

        return [
            'yaxis.labels.formatter' => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function projectsChartOptions(Collection $rows): array
    {
        $symbol = $this->currency === 'dollar' ? '$' : "\u{20AC}";

        return [
            'series' => [
                ['name' => 'Projects', 'type' => 'column', 'data' => $rows->pluck('project_count')->values()->all()],
                ['name' => 'Budgeted', 'type' => 'line', 'data' => $rows->pluck('budgeted')->values()->all()],
                ['name' => 'Booked', 'type' => 'line', 'data' => $rows->pluck('booked')->values()->all()],
            ],
            'chart' => ['type' => 'line', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#4F46E5', '#0EA5E9', '#F59E0B'],
            'stroke' => ['width' => [0, 4, 4], 'curve' => 'smooth'],
            'plotOptions' => ['bar' => ['columnWidth' => '48%', 'borderRadius' => 4]],
            'dataLabels' => ['enabled' => true, 'enabledOnSeries' => [0]],
            'xaxis' => ['categories' => $rows->pluck('year')->map(fn ($year) => (string) $year)->values()->all()],
            'yaxis' => [
                ['seriesName' => 'Projects', 'title' => ['text' => 'Projects'], 'decimalsInFloat' => 0],
                [
                    'seriesName' => ['Budgeted', 'Booked'],
                    'opposite' => true,
                    'title' => ['text' => "Financial value ({$symbol})"],
                ],
            ],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }

    private function comparisonChartOptions(Collection $rows): array
    {
        return [
            'series' => [
                ['name' => 'Budgeted', 'data' => $rows->pluck('budgeted')->values()->all()],
                ['name' => 'Approved', 'data' => $rows->pluck('approved')->values()->all()],
                ['name' => 'Booked', 'data' => $rows->pluck('booked')->values()->all()],
                ['name' => 'Committed', 'data' => $rows->pluck('committed')->values()->all()],
            ],
            'chart' => ['type' => 'area', 'height' => '100%', 'toolbar' => ['show' => false]],
            'colors' => ['#2563EB', '#8B5CF6', '#F59E0B', '#10B981'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'fill' => [
                'type' => 'gradient',
                'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.35, 'opacityTo' => 0.04],
            ],
            'dataLabels' => ['enabled' => false],
            'xaxis' => ['categories' => $rows->pluck('year')->map(fn ($year) => (string) $year)->values()->all()],
            'legend' => ['show' => true, 'position' => 'top'],
            'grid' => ['show' => true, 'borderColor' => '#E2E8F0'],
        ];
    }
}
