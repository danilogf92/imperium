<x-dashboard-chart-card title="Planned vs financial progress"
    subtitle="Elapsed milestone percentage compared with executed budget"
    :filename="$project->pda_code . '-planned-vs-financial-progress'" height="28rem">
    <livewire:livewire-column-chart key="{{ $progressComparisonChart->reactiveKey() }}"
        :column-chart-model="$progressComparisonChart" />
    <x-slot:footer>Planned {{ number_format($plannedProgress, 1) }}% · Financial {{ number_format($financialProgress, 1) }}%</x-slot:footer>
</x-dashboard-chart-card>
