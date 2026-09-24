<x-dashboard-chart-card title="{{ __('Planned vs financial progress') }}"
    subtitle="{{ __('Elapsed milestone percentage compared with executed budget') }}"
    :filename="$project->pda_code . '-planned-vs-financial-progress'" height="28rem">
    <livewire:livewire-column-chart key="{{ $progressComparisonChart->reactiveKey() }}"
        :column-chart-model="$progressComparisonChart" />
    <x-slot:footer>{{ __('Planned :planned% · Financial :financial%', ['planned' => number_format($plannedProgress, 1), 'financial' => number_format($financialProgress, 1)]) }}</x-slot:footer>
</x-dashboard-chart-card>
