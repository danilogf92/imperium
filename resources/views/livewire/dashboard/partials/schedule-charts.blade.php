@php
    $currencySymbol = $currency === 'dollar' ? '$' : '€';
@endphp

<section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <x-dashboard-chart-card title="Forecast start date vs Approved date"
        subtitle="Cumulative planned and actual project execution" filename="planned-vs-actual-execution" height="40rem">
        <livewire:livewire-line-chart key="{{ $plannedVsActualExecutionChart->reactiveKey() }}" :line-chart-model="$plannedVsActualExecutionChart" />

        <x-slot:footer>
            Cumulative percentage over a total budgeted value of
            {{ $currencySymbol }}{{ number_format($scheduleRealValueTotal, 2) }}.
        </x-slot:footer>
    </x-dashboard-chart-card>

    <x-dashboard-chart-card title="Forecast end date vs Close date" subtitle="Cumulative project completion comparison"
        filename="forecast-vs-close-date" height="40rem">
        <livewire:livewire-line-chart key="{{ $forecastVsCloseDateChart->reactiveKey() }}" :line-chart-model="$forecastVsCloseDateChart" />

        <x-slot:footer>
            Cumulative percentage over a total of
            {{ number_format($scheduleProjectTotal) }}
            projects.
        </x-slot:footer>
    </x-dashboard-chart-card>
</section>
