<x-dashboard-chart-card
    title="Financial commitment flow"
    subtitle="Budgeted, booked, executed and real value in the selected currency"
    filename="financial-commitment-flow"
    height="30rem">
    <livewire:livewire-column-chart
        key="{{ $financialFlowChart->reactiveKey() }}"
        :column-chart-model="$financialFlowChart" />
</x-dashboard-chart-card>
