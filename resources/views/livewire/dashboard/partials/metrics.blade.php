@php
    $currencySymbol = $currency === 'dollar' ? '$' : '€';

    $formatMoney = function ($value) use ($currencySymbol) {
        $value = (float) $value;

        if (abs($value) >= 1_000_000) {
            return $currencySymbol . ' ' . number_format($value / 1_000_000, 2) . ' M';
        }

        if (abs($value) >= 1_000) {
            return $currencySymbol . ' ' . number_format($value / 1_000, 2) . ' K';
        }

        return $currencySymbol . ' ' . number_format($value, 2);
    };

    $metrics = [
        [
            'label' => '',
            'secondaryLabel' => '',
            'value' => number_format($projectCount),
            'secondaryValue' => number_format($projectsWithData),
            'accent' => 'bg-fuchsia-500',
            'text' => 'text-fuchsia-700',
            'iconBg' => 'bg-fuchsia-50',
            'icon' => 'projects',
        ],
        [
            'label' => 'Budgeted',
            'value' => $formatMoney($budgeted),
            'accent' => 'bg-blue-500',
            'text' => 'text-blue-600',
            'iconBg' => 'bg-blue-50',
            'icon' => 'budgeted',
        ],
        [
            'label' => 'Approved',
            'value' => $formatMoney($executionFinishedBudget),
            'accent' => 'bg-gray-500',
            'text' => 'text-gray-600',
            'iconBg' => 'bg-gray-50',
            'icon' => 'execution-finished',
        ],
        [
            'label' => 'Booked',
            'value' => $formatMoney($booked),
            'accent' => 'bg-amber-500',
            'text' => 'text-amber-600',
            'iconBg' => 'bg-amber-50',
            'icon' => 'booked',
        ],
        [
            'label' => 'Executed',
            'value' => $formatMoney($executed),
            'accent' => 'bg-emerald-500',
            'text' => 'text-emerald-600',
            'iconBg' => 'bg-emerald-50',
            'icon' => 'executed',
        ],
        [
            'label' => 'Real (SAP)',
            'value' => $formatMoney($realValue),
            'accent' => 'bg-violet-500',
            'text' => 'text-violet-600',
            'iconBg' => 'bg-violet-50',
            'icon' => 'real',
        ],
        [
            'label' => 'Available',
            'value' => $formatMoney($executionFinishedBudget - $booked),
            'accent' => 'bg-emerald-500',
            'text' => 'text-emerald-600',
            'iconBg' => 'bg-emerald-50',
            'icon' => 'available',
        ],
    ];
@endphp

<section class="pb-1">
    <div class="dashboard-metrics-grid">
        @foreach ($metrics as $metric)
            <article
                class="relative min-h-[116px] overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                {{-- Barra de color lateral --}}
                <span class="absolute inset-y-0 left-0 w-1 {{ $metric['accent'] }}"></span>

                {{-- Encabezado --}}
                <div class="flex items-center gap-3">

                    {{-- Icono --}}
                    <span
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                            {{ $metric['iconBg'] }}
                            {{ $metric['text'] }}">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            @switch($metric['icon'])
                                @case('projects')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 9.75h16.5m-15-4.5h4.5l1.5 1.5h7.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Z" />
                                @break

                                @case('budgeted')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 7.5V6a3 3 0 0 0-3-3h-6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h9a3 3 0 0 0 3-3v-1.5m-9-9h9A1.5 1.5 0 0 1 21 9v6a1.5 1.5 0 0 1-1.5 1.5h-9a3 3 0 0 1 0-6Zm6 4.5h.008v.008H16.5V12Z" />
                                @break

                                @case('booked')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v11.25A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25A3 3 0 0 1 12 3a3 3 0 0 1 3 2.25M9 5.25V7.5h6V5.25m-6 7.5 2.25 2.25L15 10.5" />
                                @break

                                @case('executed')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                @break

                                @case('real')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 6c0 1.657-3.358 3-7.5 3S4.5 7.657 4.5 6 7.858 3 12 3s7.5 1.343 7.5 3Zm0 0v6c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3V6m15 6v6c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3v-6" />
                                @break

                                @case('execution-finished')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 12.75 9 17.25 19.5 6.75M5.25 4.5h13.5v15H5.25z" />
                                @break

                                @case('available')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6s-3 1.007-3 2.25 1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25M4.5 4.5h15v15h-15z" />
                                @break
                            @endswitch
                        </svg>
                    </span>

                    {{-- Título --}}
                    @if (isset($metric['secondaryLabel']))
                        <p class="text-sm font-medium text-slate-500">
                            {{ __('Financial / Projects') }}
                        </p>
                    @else
                        <p class="whitespace-nowrap text-sm font-medium text-slate-500">
                            {{ __($metric['label']) }}
                        </p>
                    @endif
                </div>

                {{-- Projects / Financial --}}
                @if (isset($metric['secondaryLabel']))
                    <div class="mt-3 flex items-end gap-2">

                        <div>
                            <p class="text-[11px] font-medium text-slate-400">
                                {{ __($metric['secondaryLabel']) }}
                            </p>

                            <p class="text-xl font-bold tracking-tight text-slate-900">
                                {{ $metric['secondaryValue'] }}
                            </p>
                        </div>

                        <span class="pb-0.5 text-xl font-medium text-slate-300">
                            /
                        </span>

                        <div>
                            <p class="text-[11px] font-medium text-slate-400">
                                {{ __($metric['label']) }}
                            </p>

                            <p class="text-xl font-bold tracking-tight text-slate-900">
                                {{ $metric['value'] }}
                            </p>
                        </div>

                    </div>

                    {{-- Valor financiero --}}
                @else
                    <p class="mt-3 whitespace-nowrap text-2xl font-bold tracking-tight text-slate-900"
                        title="{{ $metric['value'] }}">
                        {{ $metric['value'] }}
                    </p>
                @endif
            </article>
        @endforeach
    </div>
</section>
