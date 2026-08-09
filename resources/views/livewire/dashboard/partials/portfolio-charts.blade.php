<section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
    <x-dashboard-chart-card
        title="Projects by investment"
        subtitle="Number of projects by investment category"
        filename="projects-by-investment"
    >
        <livewire:livewire-column-chart
            key="{{ $projectsByInvestmentChart->reactiveKey() }}"
            :column-chart-model="$projectsByInvestmentChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="Budget by investment"
            subtitle="Financial distribution by investment category"
            filename="budget-by-investment"
        >
            <livewire:livewire-column-chart
                key="{{ $budgetByInvestmentChart->reactiveKey() }}"
                :column-chart-model="$budgetByInvestmentChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>

<section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
    <x-dashboard-chart-card
        title="Projects by state"
        subtitle="Current portfolio status distribution"
        filename="projects-by-state"
    >
        <livewire:livewire-pie-chart
            key="{{ $projectsByStateChart->reactiveKey() }}"
            :pie-chart-model="$projectsByStateChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="Budget by state"
            subtitle="Budget allocation across project states"
            filename="budget-by-state"
        >
            <livewire:livewire-pie-chart
                key="{{ $budgetByStateChart->reactiveKey() }}"
                :pie-chart-model="$budgetByStateChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>

<section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
    <x-dashboard-chart-card
        title="Project status count"
        subtitle="Number of projects in each state"
        filename="project-status-count"
    >
        <livewire:livewire-column-chart
            key="{{ $projectsByStateColumnChart->reactiveKey() }}"
            :column-chart-model="$projectsByStateColumnChart"
        />
    </x-dashboard-chart-card>

    @if ($hasFinancialData)
        <x-dashboard-chart-card
            title="Project status value"
            subtitle="Financial value grouped by project state"
            filename="project-status-value"
        >
            <livewire:livewire-column-chart
                key="{{ $budgetByStateColumnChart->reactiveKey() }}"
                :column-chart-model="$budgetByStateColumnChart"
            />
        </x-dashboard-chart-card>
    @endif
</section>
