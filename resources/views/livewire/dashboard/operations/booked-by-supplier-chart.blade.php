<x-dashboard-chart-card title="{{ __('Top suppliers by booked value') }}"
    subtitle="{{ __('The ten suppliers receiving the largest current commitments') }}"
    filename="top-suppliers-booked" height="30rem">
    <livewire:livewire-column-chart key="{{ $bookedBySupplierChart->reactiveKey() }}"
        :column-chart-model="$bookedBySupplierChart" />
</x-dashboard-chart-card>
