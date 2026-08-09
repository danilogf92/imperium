<x-dashboard-chart-card
    title="Committed vs available budget"
    subtitle="Share of budget already booked and the remaining uncommitted balance"
    filename="committed-vs-available-budget"
    height="30rem">
    <livewire:livewire-pie-chart
        key="{{ $budgetAvailabilityChart->reactiveKey() }}"
        :pie-chart-model="$budgetAvailabilityChart" />
</x-dashboard-chart-card>
