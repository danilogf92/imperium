<x-dashboard-chart-card title="{{ __('Projects by plant') }}"
    subtitle="{{ __('Workload distribution across the plants available to the user') }}"
    filename="projects-by-plant" height="30rem">
    <livewire:livewire-column-chart key="{{ $projectsByCompanyChart->reactiveKey() }}"
        :column-chart-model="$projectsByCompanyChart" />
</x-dashboard-chart-card>
