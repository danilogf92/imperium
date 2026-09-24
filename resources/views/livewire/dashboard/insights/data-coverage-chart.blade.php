<x-dashboard-chart-card
    title="{{ __('Project data coverage') }}"
    subtitle="{{ __('Projects with financial data compared with projects still missing data') }}"
    filename="project-data-coverage"
    height="30rem">
    <livewire:livewire-pie-chart
        key="{{ $dataCoverageChart->reactiveKey() }}"
        :pie-chart-model="$dataCoverageChart" />
</x-dashboard-chart-card>
