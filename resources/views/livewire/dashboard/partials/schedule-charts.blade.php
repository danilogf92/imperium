<section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <x-dashboard-chart-card title="{{ __('Forecast start date vs Approved date') }}"
        subtitle="{{ __('Cumulative progress lines with monthly forecast-start project bars') }}" filename="planned-vs-actual-execution" height="40rem">
        <x-dashboard-apex-chart :options="$plannedVsActualExecutionChart"
            chart-key="schedule-start-approved-{{ md5(json_encode($plannedVsActualExecutionChart)) }}" />

        <x-slot:footer>
            {{ __('Cumulative percentage over :count projects filtered by forecast start year.', ['count' => number_format($scheduleProjectTotal)]) }}
        </x-slot:footer>
    </x-dashboard-chart-card>

    <x-dashboard-chart-card title="{{ __('Forecast end date vs Close date') }}" subtitle="{{ __('Cumulative completion lines with monthly forecast-end project bars') }}"
        filename="forecast-vs-close-date" height="40rem">
        <x-dashboard-apex-chart :options="$forecastVsCloseDateChart"
            chart-key="schedule-end-close-{{ md5(json_encode($forecastVsCloseDateChart)) }}" />

        <x-slot:footer>
            {{ __('Cumulative percentage over :count projects filtered by forecast start year.', ['count' => number_format($scheduleProjectTotal)]) }}
        </x-slot:footer>
    </x-dashboard-chart-card>
</section>
