<x-dashboard-chart-card title="{{ __('Portfolio delivery stage') }}"
    subtitle="{{ __('Projects grouped into pre-execution, execution and finished') }}" filename="portfolio-delivery-stage"
    height="30rem">
    <livewire:livewire-pie-chart key="{{ $portfolioStageChart->reactiveKey() }}" :pie-chart-model="$portfolioStageChart" />
</x-dashboard-chart-card>
