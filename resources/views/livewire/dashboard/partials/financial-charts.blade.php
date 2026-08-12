<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-dashboard-chart-card title="Investment type" subtitle="Investment value by classification"
        filename="investment-type">
        <livewire:livewire-radar-chart key="{{ $budgetByInvestmentRadarChart->reactiveKey() }}" :radar-chart-model="$budgetByInvestmentRadarChart" />
    </x-dashboard-chart-card>

    @if ($hasAreaData)
        <x-dashboard-chart-card title="Area classification" subtitle="Investment distribution across project areas"
            filename="area-classification">
            <livewire:livewire-radar-chart key="{{ $budgetByAreaRadarChart->reactiveKey() }}" :radar-chart-model="$budgetByAreaRadarChart" />
        </x-dashboard-chart-card>
    @endif
</section>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    @include('livewire.dashboard.partials.cumulative-projects-budget-chart', [
        'chartData' => $cumulativeProjectsBudgetData,
        'chartKey' => 'created',
        'chartTitle' => 'Cumulative projects vs budget',
        'chartSubtitle' => 'Cumulative project progress and budget by forecast start month',
        'chartFilename' => 'cumulative-projects-vs-budget-created',
        'chartDateLabel' => 'forecast start month',
        'projectSeriesLabel' => config('dashboard_charts.cumulative_projects_budget.projects_series_label'),
        'valueSeriesLabel' => config('dashboard_charts.cumulative_projects_budget.budget_series_label'),
    ])

    @include('livewire.dashboard.partials.cumulative-projects-budget-chart', [
        'chartData' => $cumulativeApprovedProjectsBudgetData,
        'chartKey' => 'approved',
        'chartTitle' => 'Cumulative projects vs approved budget',
        'chartSubtitle' => 'Execution and Finished budget by approval month',
        'chartFilename' => 'cumulative-projects-vs-budget-approved',
        'chartDateLabel' => 'approval month',
        'projectSeriesLabel' => config('dashboard_charts.cumulative_approved_budget.projects_series_label'),
        'valueSeriesLabel' => config('dashboard_charts.cumulative_approved_budget.budget_series_label'),
    ])
</section>
