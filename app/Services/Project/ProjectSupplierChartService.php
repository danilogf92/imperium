<?php

namespace App\Services\Project;

use App\Models\Data;
use App\Support\ChartValueFormatter;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

final class ProjectSupplierChartService
{
    private const COLORS = [
        '#2563EB', '#DC2626', '#16A34A', '#D97706', '#7C3AED',
        '#DB2777', '#0891B2', '#4D7C0F', '#EA580C', '#475569',
    ];

    public function build(int $projectId, float $conversion, string $currency): array
    {
        $booked = $this->supplierValues($projectId, 'booked_euros', $conversion);
        $executed = $this->supplierValues($projectId, 'executed_euros', $conversion);

        return [
            'projectBookedBySupplierChart' => $this->chart($booked, 'Booked by supplier', $currency),
            'projectExecutedBySupplierChart' => $this->chart($executed, 'Executed by supplier', $currency),
            'hasProjectBookedSupplierData' => $booked !== [],
            'hasProjectExecutedSupplierData' => $executed !== [],
        ];
    }

    private function supplierValues(int $projectId, string $column, float $conversion): array
    {
        return Data::query()
            ->where('project_id', $projectId)
            ->whereNotNull('supplier')
            ->where('supplier', '<>', '')
            ->selectRaw("supplier AS label, COALESCE(SUM({$column}), 0) AS total")
            ->groupBy('supplier')
            ->havingRaw("SUM({$column}) <> 0")
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => round((float) $row->total * $conversion, 2),
            ])
            ->all();
    }

    private function chart(array $rows, string $title, string $currency): ColumnChartModel
    {
        $formatter = $this->moneyFormatter($currency);
        $chart = (new ColumnChartModel)
            ->setTitle($title)
            ->setAnimated(true)
            ->setHorizontal(true)
            ->setOpacity(1)
            ->setColors(self::COLORS)
            ->disableShades()
            ->withDataLabels()
            ->withGrid()
            ->setJsonConfig([
                'xaxis.labels.formatter' => $formatter,
                'dataLabels.formatter' => $formatter,
                'tooltip.y.formatter' => $formatter,
            ]);

        foreach (array_values($rows) as $index => $row) {
            $chart->addColumn(
                $row['label'],
                $row['total'],
                self::COLORS[$index % count(self::COLORS)]
            );
        }

        return $chart;
    }

    private function moneyFormatter(string $currency): string
    {
        return ChartValueFormatter::compactMoney($currency === 'dollar' ? '$' : '€');
    }
}
