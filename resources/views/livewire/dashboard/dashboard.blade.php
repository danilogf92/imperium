<div class="dashboard-page-shell">
    <div class="dashboard-page-content space-y-6">
        @include('livewire.dashboard.partials.filters')

        @include('livewire.dashboard.partials.metrics')

        @if ($hasProjects)
            @include('livewire.dashboard.partials.portfolio-charts')

            @if ($hasFinancialData)
                @include('livewire.dashboard.partials.financial-charts')
            @else
                @include('livewire.dashboard.partials.no-financial-data')
            @endif

            @include('livewire.dashboard.partials.schedule-charts')

            @include('livewire.dashboard.partials.insight-charts')

            @include('livewire.dashboard.partials.operational-charts')
        @else
            @include('livewire.dashboard.partials.no-projects')
        @endif
    </div>

    @include('livewire.dashboard.partials.download-chart-script')
</div>
