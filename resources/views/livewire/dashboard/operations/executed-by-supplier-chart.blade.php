<x-dashboard-chart-card title="Top suppliers by executed value"
    subtitle="The ten suppliers with the largest executed amounts"
    filename="top-suppliers-executed" height="30rem">
    <livewire:livewire-column-chart key="{{ $executedBySupplierChart->reactiveKey() }}"
        :column-chart-model="$executedBySupplierChart" />
</x-dashboard-chart-card>
