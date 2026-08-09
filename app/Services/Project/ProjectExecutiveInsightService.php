<?php

namespace App\Services\Project;

use App\Models\Data;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectWeeklyActivity;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Carbon\CarbonImmutable;

final class ProjectExecutiveInsightService
{
    public function build(Project $project, float $conversion, string $currency): array
    {
        $financial = $this->financialData($project->id, $conversion);
        $milestones = $this->milestones($project->id);
        $activities = $this->activities($project->id);
        $financialProgress = $this->percentage($financial['executed'], $financial['budgeted']);
        $plannedProgress = min(100, (float) $milestones['elapsed_percentage']);
        $alerts = $this->alerts($project, $financial, $activities, $milestones);

        return [
            'executiveHealth' => $this->health($project, $financial, $alerts),
            'executiveFinancial' => [
                ...$financial,
                'available' => $financial['budgeted'] - $financial['booked'],
                'real_variance' => $financial['budgeted'] - $financial['real'],
                'execution_variance' => $financial['budgeted'] - $financial['executed'],
                'execution_overrun' => max($financial['executed'] - $financial['budgeted'], 0),
                'booked_rate' => $this->percentage($financial['booked'], $financial['budgeted']),
                'execution_rate' => $financialProgress,
            ],
            'plannedProgress' => $plannedProgress,
            'financialProgress' => $financialProgress,
            'progressComparisonChart' => $this->progressChart($plannedProgress, $financialProgress),
            'executiveMilestones' => $milestones,
            'executiveActivities' => $activities,
            'executiveAlerts' => $alerts,
            'dataQuality' => $this->quality($financial),
            'executiveCurrencySymbol' => $currency === 'dollar' ? '$' : '€',
        ];
    }

    private function financialData(int $projectId, float $conversion): array
    {
        $row = Data::query()->where('project_id', $projectId)->selectRaw(
            'COUNT(*) AS rows_count, '.
            'COALESCE(SUM(global_price_euros), 0) AS budgeted, '.
            'COALESCE(SUM(booked_euros), 0) AS booked, '.
            'COALESCE(SUM(executed_euros), 0) AS executed, '.
            'COALESCE(SUM(real_value_euros), 0) AS real_value_total, '.
            "SUM(CASE WHEN supplier IS NULL OR supplier = '' THEN 1 ELSE 0 END) AS missing_supplier, ".
            "SUM(CASE WHEN order_no IS NULL OR order_no = '' THEN 1 ELSE 0 END) AS missing_order, ".
            "SUM(CASE WHEN area IS NULL OR area = '' THEN 1 ELSE 0 END) AS missing_area, ".
            "SUM(CASE WHEN general_classification IS NULL OR general_classification = '' THEN 1 ELSE 0 END) AS missing_classification, ".
            'SUM(CASE WHEN unit_price IS NULL OR unit_price = 0 THEN 1 ELSE 0 END) AS missing_price'
        )->first();

        return [
            'rows' => (int) ($row->rows_count ?? 0),
            'budgeted' => round((float) ($row->budgeted ?? 0) * $conversion, 2),
            'booked' => round((float) ($row->booked ?? 0) * $conversion, 2),
            'executed' => round((float) ($row->executed ?? 0) * $conversion, 2),
            'real' => round((float) ($row->real_value_total ?? 0) * $conversion, 2),
            'missing_supplier' => (int) ($row->missing_supplier ?? 0),
            'missing_order' => (int) ($row->missing_order ?? 0),
            'missing_area' => (int) ($row->missing_area ?? 0),
            'missing_classification' => (int) ($row->missing_classification ?? 0),
            'missing_price' => (int) ($row->missing_price ?? 0),
        ];
    }

    private function milestones(int $projectId): array
    {
        $currentPosition = now()->year * 12 + now()->month;
        $items = ProjectMilestone::query()->where('project_id', $projectId)
            ->with('milestone:id,name,code,view_color,color')
            ->orderBy('cycle_year')->orderBy('month')->orderBy('sequence')->get();

        return [
            'total' => $items->count(),
            'elapsed' => $items->filter(fn ($item) => $item->cycle_year * 12 + $item->month <= $currentPosition)->count(),
            'elapsed_percentage' => $items->filter(fn ($item) => $item->cycle_year * 12 + $item->month <= $currentPosition)->sum('percentage'),
            'upcoming' => $items->filter(fn ($item) => $item->cycle_year * 12 + $item->month >= $currentPosition)
                ->take(4)->map(fn ($item) => [
                    'name' => $item->milestone?->name,
                    'code' => $item->milestone?->code,
                    'date' => CarbonImmutable::create($item->cycle_year, $item->month)->format('M Y'),
                    'percentage' => (float) $item->percentage,
                    'color' => $item->milestone?->view_color ?: $item->milestone?->color,
                ])->values()->all(),
        ];
    }

    private function activities(int $projectId): array
    {
        $weeks = collect([0, 1])->map(function (int $offset): array {
            $date = CarbonImmutable::now()->startOfWeek()->addWeeks($offset);
            return ['year' => $date->isoWeekYear, 'week' => $date->isoWeek, 'label' => $offset ? 'Next week' : 'Actual week'];
        });
        $rows = ProjectWeeklyActivity::query()->where('project_id', $projectId)
            ->where(function ($query) use ($weeks): void {
                foreach ($weeks as $week) {
                    $query->orWhere(fn ($query) => $query->where('week_year', $week['year'])->where('week_number', $week['week']));
                }
            })->latest('id')->get();

        return $weeks->map(fn ($week) => [
            ...$week,
            'items' => $rows->where('week_year', $week['year'])->where('week_number', $week['week'])->pluck('activity')->values()->all(),
        ])->all();
    }

    private function quality(array $financial): array
    {
        $fields = ['missing_supplier', 'missing_order', 'missing_area', 'missing_classification', 'missing_price'];
        $missing = collect($fields)->sum(fn ($field) => $financial[$field]);
        $possible = $financial['rows'] * count($fields);

        return [
            'score' => $possible > 0 ? round(max(0, 100 - ($missing / $possible * 100)), 1) : 0,
            'rows' => $financial['rows'],
            'issues' => collect($fields)->mapWithKeys(fn ($field) => [str_replace('missing_', '', $field) => $financial[$field]])->all(),
        ];
    }

    private function alerts(Project $project, array $financial, array $activities, array $milestones): array
    {
        return collect([
            $financial['booked'] > $financial['budgeted'] && $financial['budgeted'] > 0 ? ['level' => 'danger', 'text' => 'Booked value exceeds the project budget.'] : null,
            $financial['real'] > $financial['budgeted'] && $financial['budgeted'] > 0 ? ['level' => 'danger', 'text' => 'Real value exceeds the project budget.'] : null,
            $financial['executed'] > $financial['budgeted'] && $financial['budgeted'] > 0 ? [
                'level' => 'danger',
                'text' => 'Executed value exceeds budget by '.number_format($financial['executed'] - $financial['budgeted'], 2).'.',
            ] : null,
            $project->forecast_end_date?->isPast() && $project->state?->value !== 'Finished' ? ['level' => 'danger', 'text' => 'Forecast end date has passed and the project is not finished.'] : null,
            $financial['rows'] === 0 ? ['level' => 'warning', 'text' => 'The project has no financial data.'] : null,
            collect($activities)->sum(fn ($week) => count($week['items'])) === 0 ? ['level' => 'warning', 'text' => 'No activities are registered for the current or next week.'] : null,
            $milestones['total'] === 0 ? ['level' => 'warning', 'text' => 'The project has no planning milestones.'] : null,
        ])->filter()->values()->all();
    }

    private function health(Project $project, array $financial, array $alerts): array
    {
        $danger = collect($alerts)->contains(fn ($alert) => $alert['level'] === 'danger');
        $level = $project->state?->value === 'Postponed' || $danger ? 'danger' : ($alerts !== [] ? 'warning' : 'healthy');

        return match ($level) {
            'danger' => ['label' => 'Attention required', 'color' => 'red'],
            'warning' => ['label' => 'Needs review', 'color' => 'amber'],
            default => ['label' => 'On track', 'color' => 'emerald'],
        };
    }

    private function progressChart(float $planned, float $financial): ColumnChartModel
    {
        $axisMaximum = max(100, (int) ceil(max($planned, $financial) / 10) * 10);

        return (new ColumnChartModel)->setTitle('Planned vs financial progress')->setAnimated(true)
            ->setOpacity(1)->setColors(['#2563EB', '#16A34A'])->disableShades()
            ->withDataLabels()->withGrid()->addColumn('Planned milestones', round($planned, 1), '#2563eb')
            ->addColumn('Financial execution', round($financial, 1), '#16a34a')
            ->setJsonConfig([
                'yaxis.max' => $axisMaximum,
                'yaxis.labels.formatter' => "function(value) { return Number(value).toFixed(0) + '%'; }",
                'dataLabels.formatter' => "function(value) { return Number(value).toFixed(1) + '%'; }",
                'tooltip.y.formatter' => "function(value) { return Number(value).toFixed(1) + '%'; }",
            ]);
    }

    private function percentage(float $value, float $total): float
    {
        return $total > 0 ? round($value / $total * 100, 1) : 0;
    }
}
