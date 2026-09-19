<div class="dashboard-page-shell">
    <div class="dashboard-page-content space-y-6">
        @include('livewire.dashboard.partials.filters')

        @include('livewire.dashboard.partials.metrics')

        @if ($hasProjects)
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-dashboard-chart-card title="Projects by state" subtitle="Current portfolio status distribution"
                    filename="review-projects-by-state">
                    <livewire:livewire-pie-chart key="review-{{ $projectsByStateChart->reactiveKey() }}"
                        :pie-chart-model="$projectsByStateChart" />
                </x-dashboard-chart-card>

                @if ($hasFinancialData)
                    <x-dashboard-chart-card title="Budget by state" subtitle="Budget allocation across project states"
                        filename="review-budget-by-state">
                        <livewire:livewire-pie-chart key="review-{{ $budgetByStateChart->reactiveKey() }}"
                            :pie-chart-model="$budgetByStateChart" />
                    </x-dashboard-chart-card>
                @endif
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-dashboard-chart-card title="Project status count" subtitle="Number of projects in each state"
                    filename="review-project-status-count">
                    <livewire:livewire-column-chart key="review-{{ $projectsByStateColumnChart->reactiveKey() }}"
                        :column-chart-model="$projectsByStateColumnChart" />
                </x-dashboard-chart-card>

                @if ($hasFinancialData)
                    <x-dashboard-chart-card title="Project status value" subtitle="Financial value grouped by project state"
                        filename="review-project-status-value">
                        <livewire:livewire-column-chart key="review-{{ $budgetByStateColumnChart->reactiveKey() }}"
                            :column-chart-model="$budgetByStateColumnChart" />
                    </x-dashboard-chart-card>
                @endif
            </section>
        @else
            @include('livewire.dashboard.partials.no-projects')
        @endif
    </div>

    @include('livewire.dashboard.partials.download-chart-script')
</div>
