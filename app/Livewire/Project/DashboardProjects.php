<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Exports\ProjectDetailDashboardExport;
use App\Models\Data;
use App\Models\Project;
use App\Models\UserPreference;
use App\Services\Project\ProjectExecutiveInsightService;
use App\Services\Project\ProjectSupplierChartService;
use App\Support\ChartValueFormatter;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\RadarChartModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardProjects extends Component
{
    use WithFileUploads;
    use WithPagination;

    private const CURRENCY_PREFERENCE_PREFIX = 'projects.dashboard.currency.';

    public $resumeColors = [
        'Budgeted' => '#5BCA5A',
        'Executed' => '#CC5555',
        'Assigned' => '#FFA500',
        'Booked (Real SAP)' => '#800080',
    ];

    public $resume = [
        'Budgeted',
        'Executed',
        'Assigned',
        'Booked (Real SAP)',
    ];

    public $resumePie = [
        'Booked (Real SAP)',
        'Rest',
        'Assigned',
    ];

    public $resumePieColors = [
        'Booked (Real SAP)' => '#5BCA5A',
        'Rest' => '#CC5555',
        'Assigned' => '#5BCA5A',

    ];

    public $project;

    public $firstRun = true;

    public $total = 0;

    public $columnNames;

    public $searchData = 'area';

    public $investments = 'global_price_euros';

    public $percentage = 0;

    public $real_value = 0;

    public $rateValue = 1;

    public $rateConvertion = 1;

    public $dollarOrEuro = 'euro';

    public $budgeted = 0;

    public $booked = 0;

    public $executed = 0;

    public $graph;

    private const GROUP_COLUMNS = [
        'area',
        'group_1',
        'group_2',
        'general_classification',
        'item_type',
        'stage',
        'supplier',
    ];

    private const VALUE_COLUMNS = [
        'global_price_euros',
        'real_value_euros',
        'booked_euros',
        'executed_euros',
    ];

    private const CHART_CATEGORY_COLORS = [
        '#1D4ED8', '#DC2626', '#059669', '#D97706', '#7C3AED',
        '#DB2777', '#0891B2', '#65A30D', '#EA580C', '#475569',
        '#0F766E', '#9333EA', '#BE123C', '#4D7C0F', '#0369A1',
        '#B45309', '#4338CA', '#15803D', '#A21CAF', '#334155',
    ];

    public function mount(Project $project)
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $this->project = Project::query()
            ->with('company:id,company_name,company_code')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::View
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($project->getKey());

        $this->rateValue = $this->safeDivide(1, max((float) $this->project->rate, 0.01), 1);
        $this->dollarOrEuro = $this->storedCurrency();
        $this->rateConvertion = $this->dollarOrEuro === 'dollar'
            ? $this->safeDivide(1, $this->rateValue, 1)
            : 1;

        $this->total = $this->getValueBySearch('global_price_euros');
        $this->real_value = $this->getValueBySearch('real_value_euros');
        $this->booked = $this->getValueBySearch('booked_euros');
        $this->executed = $this->getValueBySearch('executed_euros');
        $this->budgeted = $this->total;

        if ($this->total != 0) {
            // $this->percentage = round($this->booked / $this->total * 100, 2);
            $this->percentage = round($this->safeDivide($this->executed, $this->total) * 100, 2);
        } else {
            $this->percentage = 0;
        }

        $this->columnNames = self::GROUP_COLUMNS;

    }

    public function updated($property, $value)
    {
        if (! in_array($this->searchData, self::GROUP_COLUMNS, true)) {
            $this->searchData = 'area';
        }

        if (! in_array($this->investments, self::VALUE_COLUMNS, true)) {
            $this->investments = 'global_price_euros';
        }

        if (! in_array($this->dollarOrEuro, ['euro', 'dollar'], true)) {
            $this->dollarOrEuro = 'euro';
        }

        $this->rateConvertion = $this->dollarOrEuro === 'dollar'
            ? $this->safeDivide(1, $this->rateValue, 1)
            : 1;

        if ($property === 'dollarOrEuro') {
            $this->saveCurrencyPreference();
        }

        $this->total = $this->getValueBySearch('global_price_euros');
        $this->real_value = $this->getValueBySearch('real_value_euros');
        $this->booked = $this->getValueBySearch('booked_euros');
        $this->executed = $this->getValueBySearch('executed_euros');
        $this->budgeted = $this->total;

        if ($this->total != 0) {
            $this->percentage = round($this->safeDivide($this->executed, $this->total) * 100, 2);
        } else {
            $this->percentage = 0;
        }
    }

    public function render(
        ProjectSupplierChartService $supplierCharts,
        ProjectExecutiveInsightService $executiveInsights
    ) {
        $area = Data::query()
            ->where('project_id', $this->project->id)
            ->select($this->searchData)
            ->selectRaw("SUM({$this->investments}) as total")
            ->groupBy($this->searchData)
            ->get()
            ->map(fn ($item) => [
                $this->searchData => $item->{$this->searchData},
                'total' => (float) $item->total,
            ])
            ->sortByDesc('total')
            ->values();

        $title = 'Resume_'.(($this->dollarOrEuro === 'euro') ? '€' : '$');

        $graph = $this->radarDataGraph($area);
        $this->graph = $area;

        // dd($area);

        return view('livewire.project.dashboard-projects')
            ->with([
                'canExportReport' => auth()->user()
                    ?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                    ->whereKey($this->project->company_id)
                    ->exists() ?? false,
                'hasOrders' => Data::query()
                    ->where('project_id', $this->project->id)
                    ->whereNotNull('order_no')
                    ->where('order_no', '<>', '')
                    ->exists(),
                'radarChartModel' => $graph,
                'columnChartModel' => $this->classificationBudgetRealGraph(),
                'resumeGraph' => $this->columnDataGraphTwo($this->createResumeGraph(), $title),
                'resumePercentageGraph' => $this->columnDataGraphTwo($this->createResumeGraph('%'), 'Resume %', true),
                'pieChartModel' => $this->pieDataGraph($area, $this->rateConvertion),
                'pieChartModelResume' => $this->pieDataGraphTwo($this->createResumePieGraph(), $this->rateConvertion, 'Account Balance With Booked (Real SAP)'),
                'pieChartModelResumeTwo' => $this->pieDataGraphTwo($this->createResumePieGraphTwo(), $this->rateConvertion, 'Account Balance With Assigned'),
                'multiColumnChartModel' => $this->multicolumnDataGraph(),
                ...$supplierCharts->build(
                    $this->project->id,
                    (float) $this->rateConvertion,
                    $this->dollarOrEuro
                ),
                ...$executiveInsights->build(
                    $this->project,
                    (float) $this->rateConvertion,
                    $this->dollarOrEuro
                ),
            ])
            ->layout('layouts.app');
    }

    public function resetAll(): void
    {
        $this->searchData = 'area';
        $this->investments = 'global_price_euros';
        $this->updated('resetAll', null);
    }

    #[Renderless]
    public function exportReport(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                ->whereKey($this->project->company_id)
                ->exists(),
            403
        );

        return (new ProjectDetailDashboardExport)->download(
            $this->project,
            $this->searchData,
            $this->investments,
            $this->dollarOrEuro,
            $this->rateConvertion
        );
    }

    private function groupTitle(): string
    {
        return $this->textTransform($this->searchData);
    }

    private function valueTitle(): string
    {
        $label = match ($this->investments) {
            'booked_euros' => 'Assigned',
            'real_value_euros' => 'Booked (Real SAP)',
            default => null,
        };
        if ($label !== null) {
            return $label.($this->dollarOrEuro === 'dollar' ? ' (USD)' : ' (EUR)');
        }

        $value = $this->dollarOrEuro === 'dollar'
            ? str_replace('_euros', '_dollars', $this->investments)
            : str_replace('_dollars', '_euros', $this->investments);

        return $this->textTransform($value);
    }

    public function downloadChart()
    {
        $graph = $this->radarDataGraph($this->graph);
        $chartImage = $graph->toBase64Image();

        // Guarda la imagen en almacenamiento temporal
        Storage::put('charts/chart.png', $chartImage);

        // Descarga la imagen
        return response()->download(storage_path('app/charts/chart.png'));
    }

    public function formatText($texto)
    {
        // Elimina guiones bajos y convierte el texto en un array de palabras
        $palabras = explode('_', $texto);

        // Convierte la primera letra de cada palabra en mayúscula
        $palabras = array_map('ucfirst', $palabras);

        // Une las palabras nuevamente en un solo string
        $textoTransformado = implode(' ', $palabras);

        return $textoTransformado;
    }

    public function getValueBySearch($value)
    {
        $value = Data::where('project_id', $this->project->id)->sum($value);

        if ($this->dollarOrEuro === 'dollar') {
            return round($value * $this->rateConvertion, 2);
        } else {
            return round($value, 2);
        }
    }

    public function multicolumnDataGraph()
    {
        $values = Data::query()
            ->where('project_id', $this->project->id)
            ->select(
                $this->searchData,
                DB::raw('SUM(executed_euros) as executedValue'),
                DB::raw('SUM(global_price_euros) as budgetedValue'),
                DB::raw('SUM(booked_euros) as bookedValue'),
                DB::raw('SUM(real_value_euros) as realValue')
            )
            ->groupBy($this->searchData)
            ->get();

        $multiColumnChartModel = $values
            ->reduce(

                function ($multiColumnChartModel, $data) {
                    $type = $data->{$this->searchData};

                    // Verifica si $type es null y omite la iteración si es el caso
                    if ($type === null) {
                        return $multiColumnChartModel;
                    }

                    $executedValue = $this->convertAndUpdate($data->executedValue);
                    $budgetedValue = $this->convertAndUpdate($data->budgetedValue);
                    $bookedValue = $this->convertAndUpdate($data->bookedValue);
                    $realValue = $this->convertAndUpdate($data->realValue);

                    return $multiColumnChartModel
                        ->addSeriesColumn($type, 'Budgeted', $budgetedValue)
                        ->addSeriesColumn($type, 'Executed', $executedValue)
                        ->addSeriesColumn($type, 'Assigned', $bookedValue)
                        ->addSeriesColumn($type, 'Booked (Real SAP)', $realValue);
                },
                LivewireCharts::multiColumnChartModel()
                    ->setAnimated($this->firstRun)
                    ->withOnColumnClickEventName('onColumnClick')
                    // ->setTitle('Comparison')
                    ->setTitle('Resume for '.$this->groupTitle())
                    ->stacked()
                    ->withGrid()
                    ->withDataLabels()
                    ->withLegend()
                    ->legendPositionTop()
                    ->setOpacity(1)
                    ->setColors(self::CHART_CATEGORY_COLORS)
                    ->disableShades()
                    ->setJsonConfig($this->moneyChartConfig('yaxis'))
            );

        return $multiColumnChartModel;
    }

    public function radarDataGraph($data)
    {
        if ($this->dollarOrEuro === 'euro') {
            $titulo = $this->valueTitle();
            $label = 'Investment €';
        } else {
            $titulo = $this->valueTitle();
            $label = 'Investment $';
        }

        $radarChartModel = LivewireCharts::radarChartModel()
            ->setTitle($this->groupTitle().' -> '.$this->valueTitle())
            ->setAnimated($this->firstRun)
            ->withOnPointClickEvent('onPointClick')
            ->withGrid()
            // ->withDataLabels()
            ->withLegend()
            ->legendPositionTop()
            ->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($data as $element) {
            if ($this->validateNumber($element['total']) && $element[$this->searchData] != null) {
                $radarChartModel->addSeries($label, $element[$this->searchData], $this->convertAndUpdate($element['total']));
            }
        }

        return $radarChartModel;
    }

    // public function radarDataGraphSave()
    // {
    //     $constantData = [
    //         ['total' => 50, 'searchData' => 'Value1'],
    //         ['total' => 75, 'searchData' => 'Value2'],
    //         ['total' => 30, 'searchData' => 'Value3'],
    //         ['total' => 90, 'searchData' => 'Value4'],
    //     ];

    //     if ($this->dollarOrEuro === 'euro') {
    //         $label = "Investment €";
    //     } else {
    //         $label = "Investment $";
    //     }

    //     $radarChartModel = LivewireCharts::radarChartModel()
    //         // ->setTitle($this->textTransform($this->searchData) . " -> " . $this->textTransform($titulo))
    //         ->setAnimated($this->firstRun)
    //         ->withOnPointClickEvent('onPointClick')
    //         ->withGrid()
    //         // ->withDataLabels()
    //         ->withLegend()
    //         ->legendPositionTop();

    //     foreach ($constantData as $element) {
    //         if ($this->validateNumber($element['total']) && $element['searchData'] != null) {
    //             $radarChartModel->addSeries($label, $element['searchData'], $this->convertAndUpdate($element['total']));
    //         }
    //     }

    //     $image = $radarChartModel->toBase64('png'); // Convierte la gráfica a formato PNG

    //     $base64Image = base64_decode($image);

    //     return response()->stream(
    //         function () use ($base64Image) {
    //             echo $base64Image;
    //         },
    //         200,
    //         [
    //             'Content-Type' => 'image/png',
    //             'Content-Disposition' => 'attachment; filename=grafica.png',
    //         ]
    //     );
    // }

    public function pieDataGraph($data, $rate)
    {
        if ($this->dollarOrEuro === 'euro') {
            $titulo = $this->valueTitle();
        } else {
            $titulo = $this->valueTitle();
        }

        $pieChartModel =
            (new PieChartModel)
            // ->setTitle($this->searchData . " " . $this->investments)
                ->setTitle($this->groupTitle().' -> '.$this->valueTitle())
                ->setAnimated($this->firstRun)
                ->setLegendVisibility(true)
                ->withOnSliceClickEvent('onSliceClick')
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->setType('donut')
                ->setJsonConfig($this->moneyPieChartConfig());

        foreach ($data as $element) {
            if ($this->validateNumber($element['total']) && $element[$this->searchData] != null) {
                $pieChartModel->addSlice($element[$this->searchData], round($element['total'] * $rate, 2), $this->generateColor());
            }
        }

        return $pieChartModel;
    }

    public function pieDataGraphTwo($data, $rate, $title)
    {
        $pieChartModel =
            (new PieChartModel)
            // ->setTitle($this->searchData . " " . $this->investments)
                ->setTitle($title)
                ->setAnimated($this->firstRun)
                ->setLegendVisibility(true)
                ->withOnSliceClickEvent('onSliceClick')
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->setType('donut')
                ->setJsonConfig($this->moneyPieChartConfig());

        foreach ($data as $element) {
            if ($this->validateNumberNew($element['total']) && $element['label'] != null) {
                $pieChartModel->addSlice($element['label'], round($element['total'] * $rate, 2), $this->resumePieColors[$element['label']]);
            }
        }

        return $pieChartModel;
    }

    public function columnDataGraph($data, $title)
    {
        $columnChartModel =
            (new ColumnChartModel)
            // ->addColumn('Total', $this->total, $this->generateColor())
                ->withOnColumnClickEventName('onColumnClick')
            // ->setTitle($this->searchData . " " . $this->investments)
            // ->setTitle("Clasification for " . $this->textTransform($this->searchData))
                ->setTitle($this->textTransform($title))
                ->setAnimated($this->firstRun)
                ->setLegendVisibility(true)
                ->setOpacity(1)
                ->disableShades()
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->legendPositionTop()
                ->setDataLabelsEnabled(true)
                ->setJsonConfig($this->moneyChartConfig('yaxis'));

        foreach ($data as $element) {
            if ($this->validateNumber($element['total']) && $element[$this->searchData] != null) {
                $columnChartModel->addColumn($element[$this->searchData], round((float) $element['total'], 2), $this->generateColor());
            }
        }

        return $columnChartModel;
    }

    public function columnDataGraphTwo($data, $title, bool $percentage = false)
    {
        $columnChartModel =
            (new ColumnChartModel)
                ->withOnColumnClickEventName('onColumnClick')
                ->setTitle($this->textTransform($title))
                ->setAnimated($this->firstRun)
                ->setLegendVisibility(true)
                ->setOpacity(1)
                ->disableShades()
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->legendPositionTop()
                ->setDataLabelsEnabled(true)
                ->setJsonConfig(
                    $percentage
                        ? $this->percentChartConfig('yaxis')
                        : $this->moneyChartConfig('yaxis')
                );

        foreach ($data as $element) {
            if ($this->validateNumber($element['total']) && $element['label'] != null) {
                $columnChartModel->addColumn($element['label'], round((float) $element['total'], 2), $this->resumeColors[$element['label']]);
            }
        }

        return $columnChartModel;
    }

    public function generateColor()
    {
        // Genera tres valores hexadecimales aleatorios para los componentes rojo, verde y azul
        $rojo = str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
        $verde = str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
        $azul = str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
        // Combina los valores para obtener el color completo en formato hexadecimal
        $colorHexadecimal = "#$rojo$verde$azul";

        return $colorHexadecimal;
    }

    public function validateNumber($number)
    {
        return is_numeric($number) && ! is_nan($number) && $number > 0;
    }

    public function validateNumberNew($number)
    {
        return is_numeric($number) && ! is_nan($number);
    }

    public function convertAndUpdate($value)
    {
        return round(((float) $value * $this->rateConvertion), 2);
    }

    public function textTransform($cadena)
    {
        $palabras = explode('_', $cadena);
        $palabrasCapitalizadas = array_map('ucfirst', $palabras);
        $resultado = implode(' ', $palabrasCapitalizadas);

        return $resultado;
    }

    public function createResumeGraph($percentage = null)
    {
        if (! $percentage) {
            $conversion = 1;
        } else {
            if (($this->total > 0)) {
                $conversion = $this->safeDivide(100, $this->total);
            } else {
                $conversion = 1;
            }
        }

        return [
            [
                'label' => 'Budgeted',
                'total' => round($this->budgeted * $conversion, 2),
            ],
            [
                'label' => 'Executed',
                'total' => round($this->executed * $conversion, 2),
            ],
            [
                'label' => 'Assigned',
                'total' => round($this->booked * $conversion, 2),
            ],
            [
                'label' => 'Booked (Real SAP)',
                'total' => round($this->real_value * $conversion, 2),
            ],
        ];
    }

    public function createResumePieGraph()
    {
        $conversion = 1;
        $rateConvertion = $this->safeDivisor($this->rateConvertion);

        return [
            [
                'label' => 'Booked (Real SAP)',
                'total' => round(
                    $this->safeDivide($this->real_value * $conversion, $rateConvertion),
                    2
                ),
            ],
            [
                'label' => 'Rest',
                'total' => round(
                    $this->safeDivide(
                        ($this->budgeted - $this->real_value) * $conversion,
                        $rateConvertion
                    ),
                    2
                ),
            ],
        ];
    }

    public function classificationBudgetRealGraph(): ColumnChartModel
    {
        $values = Data::query()
            ->where('project_id', $this->project->id)
            ->whereNotNull($this->searchData)
            ->select($this->searchData)
            ->selectRaw('SUM(global_price_euros) as budgeted_value')
            ->selectRaw('SUM(real_value_euros) as real_value')
            ->groupBy($this->searchData)
            ->orderBy($this->searchData)
            ->get();

        return $values->reduce(
            function (ColumnChartModel $chart, Data $data): ColumnChartModel {
                $group = (string) $data->{$this->searchData};

                return $chart
                    ->addSeriesColumn($group, 'Budgeted', $this->convertAndUpdate($data->budgeted_value))
                    ->addSeriesColumn($group, 'Booked (Real SAP)', $this->convertAndUpdate($data->real_value));
            },
            LivewireCharts::multiColumnChartModel()
                ->setTitle("Budgeted vs Booked (Real SAP) by {$this->groupTitle()}")
                ->setAnimated($this->firstRun)
                ->withOnColumnClickEventName('onColumnClick')
                ->stacked()
                ->withGrid()
                ->withDataLabels()
                ->withLegend()
                ->legendPositionTop()
                ->setColors(self::CHART_CATEGORY_COLORS)
                ->disableShades()
                ->setJsonConfig($this->moneyChartConfig('yaxis'))
        );
    }

    public function createResumePieGraphTwo()
    {
        $conversion = 1;
        $rateConvertion = $this->safeDivisor($this->rateConvertion);

        return [
            [
                'label' => 'Assigned',
                'total' => round(
                    $this->safeDivide($this->booked, $rateConvertion) * $conversion,
                    2
                ),
            ],
            [
                'label' => 'Rest',
                'total' => round(
                    $this->safeDivide(
                        $this->budgeted - $this->booked,
                        $rateConvertion
                    ) * $conversion,
                    2
                ),
            ],
        ];
    }

    private function safeDivide(float|int $numerator, mixed $denominator, float $fallback = 0): float
    {
        $denominator = (float) $denominator;

        if (abs($denominator) < 0.0000001) {
            return $fallback;
        }

        return (float) $numerator / $denominator;
    }

    private function safeDivisor(mixed $value): float
    {
        $value = (float) $value;

        return abs($value) < 0.0000001 ? 1 : $value;
    }

    private function storedCurrency(): string
    {
        $currency = UserPreference::query()
            ->where('user_id', auth()->id())
            ->where('key', $this->currencyPreferenceKey())
            ->first()?->value['currency'] ?? 'euro';

        return in_array($currency, ['euro', 'dollar'], true) ? $currency : 'euro';
    }

    private function saveCurrencyPreference(): void
    {
        UserPreference::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'key' => $this->currencyPreferenceKey(),
            ],
            ['value' => ['currency' => $this->dollarOrEuro]]
        );
    }

    private function currencyPreferenceKey(): string
    {
        return self::CURRENCY_PREFERENCE_PREFIX.$this->project->getKey();
    }

    private function moneyChartConfig(string $axis): array
    {
        $formatter = $this->moneyFormatter();

        return [
            "{$axis}.labels.formatter" => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function percentChartConfig(string $axis): array
    {
        $formatter = $this->percentFormatter();

        return [
            "{$axis}.labels.formatter" => $formatter,
            'dataLabels.formatter' => $formatter,
            'tooltip.y.formatter' => $formatter,
        ];
    }

    private function moneyPieChartConfig(): array
    {
        return [
            'dataLabels.formatter' => $this->percentFormatter(),
            'tooltip.y.formatter' => $this->moneyFormatter(),
        ];
    }

    private function moneyFormatter(): string
    {
        return ChartValueFormatter::compactMoney(
            $this->dollarOrEuro === 'dollar' ? '$' : '€'
        );
    }

    private function percentFormatter(): string
    {
        return "function(value) { return Number(value).toFixed(1) + '%'; }";
    }

    public function placeholder()
    {
        return <<<'HTML'
                <div class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-stone-200">
                    <div class="p-4 rounded">
                        <p class="text-3xl font-extrabold">Loading....</p>
                    </div>
                </div>
        HTML;
    }
}
