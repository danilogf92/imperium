<section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
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
        'chartSubtitle' => 'Cumulative project progress and budget by creation month',
        'chartFilename' => 'cumulative-projects-vs-budget-created',
        'chartDateLabel' => 'project creation month',
        'valueSeriesLabel' => 'Cumulative budget',
    ])

    @include('livewire.dashboard.partials.cumulative-projects-budget-chart', [
        'chartData' => $cumulativeApprovedProjectsBudgetData,
        'chartKey' => 'approved',
        'chartTitle' => 'Cumulative projects vs approved budget',
        'chartSubtitle' => 'Execution and Finished budget by approval month',
        'chartFilename' => 'cumulative-projects-vs-budget-approved',
        'chartDateLabel' => 'approval month (updated month when approval date is missing)',
        'valueSeriesLabel' => 'Cumulative approved',
    ])
</section>
