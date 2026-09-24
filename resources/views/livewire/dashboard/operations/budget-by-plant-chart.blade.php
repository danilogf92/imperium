<x-dashboard-chart-card title="{{ __('Budget by plant') }}"
    subtitle="{{ __('Capital allocation across plants in the selected currency') }}"
    filename="budget-by-plant" height="30rem">
    <livewire:livewire-column-chart key="{{ $budgetByCompanyChart->reactiveKey() }}"
        :column-chart-model="$budgetByCompanyChart" />
</x-dashboard-chart-card>
