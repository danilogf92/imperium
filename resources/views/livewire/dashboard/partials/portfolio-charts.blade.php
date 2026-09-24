<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-dashboard-chart-card
        title="{{ __('Projects by investment') }}"
        subtitle="{{ __('Number of projects by investment category') }}"
        filename="projects-by-investment"
    >
        <livewire:livewire-column-chart
            key="{{ $projectsByInvestmentChart->reactiveKey() }}"
            :column-chart-model="$projectsByInvestmentChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="{{ __('Budget by investment') }}"
            subtitle="{{ __('Financial distribution by investment category') }}"
            filename="budget-by-investment"
        >
            <livewire:livewire-column-chart
                key="{{ $budgetByInvestmentChart->reactiveKey() }}"
                :column-chart-model="$budgetByInvestmentChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-dashboard-chart-card
        title="{{ __('Projects by state') }}"
        subtitle="{{ __('Current portfolio status distribution') }}"
        filename="projects-by-state"
    >
        <livewire:livewire-pie-chart
            key="{{ $projectsByStateChart->reactiveKey() }}"
            :pie-chart-model="$projectsByStateChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="{{ __('Budget by state') }}"
            subtitle="{{ __('Budget allocation across project states') }}"
            filename="budget-by-state"
        >
            <livewire:livewire-pie-chart
                key="{{ $budgetByStateChart->reactiveKey() }}"
                :pie-chart-model="$budgetByStateChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-dashboard-chart-card
        title="{{ __('Project status count') }}"
        subtitle="{{ __('Number of projects in each state') }}"
        filename="project-status-count"
    >
        <livewire:livewire-column-chart
            key="{{ $projectsByStateColumnChart->reactiveKey() }}"
            :column-chart-model="$projectsByStateColumnChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="{{ __('Project status value') }}"
            subtitle="{{ __('Financial value grouped by project state') }}"
            filename="project-status-value"
        >
            <livewire:livewire-column-chart
                key="{{ $budgetByStateColumnChart->reactiveKey() }}"
                :column-chart-model="$budgetByStateColumnChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>
