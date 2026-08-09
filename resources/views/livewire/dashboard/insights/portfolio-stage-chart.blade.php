<x-dashboard-chart-card
    title="Portfolio delivery stage"
    subtitle="Projects grouped into pre-execution, execution, finished and postponed"
    filename="portfolio-delivery-stage"
    height="30rem">
    <livewire:livewire-pie-chart
        key="{{ $portfolioStageChart->reactiveKey() }}"
        :pie-chart-model="$portfolioStageChart" />
</x-dashboard-chart-card>
