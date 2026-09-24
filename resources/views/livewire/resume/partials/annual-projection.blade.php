@if ($projectionRows !== [])
    <x-dashboard-chart-card title="cash_flow_projection.title" subtitle="cash_flow_projection.subtitle"
        filename="annual-cash-flow-projection" height="auto" class="annual-projection-chart lg:col-span-2">
        <div class="space-y-4">
            <p class="text-sm text-slate-600">{{ __('cash_flow_projection.explanation', ['month' => now()->startOfMonth()->translatedFormat('F')]) }}</p>
            <div class="h-80 min-h-80 sm:h-[300px] sm:min-h-[300px]">
                <x-dashboard-apex-chart :options="$projectionChartOptions"
                    chart-key="resume-annual-projection-{{ md5(json_encode($projectionChartOptions)) }}" />
            </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full whitespace-nowrap text-right text-xs">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('cash_flow_projection.series') }}</th>
                            @foreach ($projectionRows as $row)
                                <th scope="col" class="px-3 py-2">{{ \Carbon\CarbonImmutable::createFromFormat('!Y-m', $row['period'])->translatedFormat('M Y') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['planned', 'actual', 'projected'] as $field)
                            <tr class="border-t border-slate-200 {{ $field === 'projected' ? 'bg-violet-50 font-semibold text-violet-900' : 'text-slate-700' }}">
                                <th scope="row" class="px-3 py-2 text-left">{{ __('cash_flow_projection.'.$field) }}</th>
                                @foreach ($projectionRows as $row)
                                    <td class="px-3 py-2 tabular-nums">{{ $row[$field] === null ? '—' : $currencySymbol.' '.number_format($row[$field], 2) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full whitespace-nowrap text-right text-xs">
                    <caption class="px-3 py-2 text-left font-semibold text-slate-800">{{ __('cash_flow_projection.calculation') }}</caption>
                    <thead class="bg-slate-100 text-slate-700"><tr>
                        @foreach (['year', 'closed_months', 'closed_planned', 'closed_actual', 'difference', 'remaining_months', 'per_month'] as $field)
                            <th scope="col" class="px-3 py-2">{{ __('cash_flow_projection.'.$field) }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                        @foreach ($projectionSummaries as $summary)
                            <tr class="border-t border-slate-200 text-slate-700">
                                @foreach (['year', 'closed_months', 'closed_planned', 'closed_actual', 'difference', 'remaining_months', 'per_month'] as $field)
                                    <td class="px-3 py-2 tabular-nums {{ $field === 'per_month' ? 'font-bold text-violet-800' : '' }}">
                                        {{ in_array($field, ['year', 'closed_months', 'remaining_months']) ? $summary[$field] : $currencySymbol.' '.number_format($summary[$field], $field === 'per_month' ? 4 : 2) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <x-slot:footer>{{ __('cash_flow_projection.formula') }}</x-slot:footer>
    </x-dashboard-chart-card>
@endif
