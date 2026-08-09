<x-dashboard-chart-card title="Projects by plant"
    subtitle="Workload distribution across the plants available to the user"
    filename="projects-by-plant" height="30rem">
    <livewire:livewire-column-chart key="{{ $projectsByCompanyChart->reactiveKey() }}"
        :column-chart-model="$projectsByCompanyChart" />
</x-dashboard-chart-card>
