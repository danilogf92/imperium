<x-dashboard-chart-card title="Top suppliers by booked value"
    subtitle="Top commitments for project {{ $project->pda_code }}"
    :filename="$project->pda_code . '-top-suppliers-booked'"
    height="30rem">
    <livewire:livewire-column-chart
        key="{{ $projectBookedBySupplierChart->reactiveKey() }}"
        :column-chart-model="$projectBookedBySupplierChart" />
</x-dashboard-chart-card>
