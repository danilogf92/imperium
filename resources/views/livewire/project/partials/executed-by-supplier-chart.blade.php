<x-dashboard-chart-card title="Top suppliers by executed value"
    subtitle="Top executed amounts for project {{ $project->pda_code }}"
    :filename="$project->pda_code . '-top-suppliers-executed'"
    height="30rem">
    <livewire:livewire-column-chart
        key="{{ $projectExecutedBySupplierChart->reactiveKey() }}"
        :column-chart-model="$projectExecutedBySupplierChart" />
</x-dashboard-chart-card>
