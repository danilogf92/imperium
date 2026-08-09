<div class="dashboard-page-shell">
    <style>
        .project-dashboard-action {
            transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
        }

        .project-dashboard-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
            filter: brightness(1.06);
        }

        .project-dashboard-action:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.16);
            filter: brightness(0.96);
        }

        .project-dashboard-action:focus-visible {
            outline: 3px solid rgba(59, 130, 246, 0.35);
            outline-offset: 2px;
        }

        .back-to-projects-action {
            background-color: #eab308;
            border-color: #ca8a04;
            color: #422006;
        }

        .back-to-projects-action:hover {
            background-color: #facc15;
            border-color: #eab308;
            color: #422006;
        }

        .back-to-projects-action:active {
            background-color: #ca8a04;
        }

        .project-orders-disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .project-orders-disabled:hover {
            background-color: #eab308;
            border-color: #ca8a04;
            box-shadow: none;
            filter: none;
            transform: none;
        }

        .project-orders-tooltip-wrapper {
            position: relative;
        }

        .project-orders-tooltip {
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            z-index: 40;
            width: max-content;
            max-width: 220px;
            padding: 8px 11px;
            border-radius: 8px;
            background-color: #0f172a;
            color: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
            text-align: center;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.24);
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, 5px);
            transition: opacity 150ms ease, transform 150ms ease;
        }

        .project-orders-tooltip::after {
            position: absolute;
            top: 100%;
            left: 50%;
            width: 8px;
            height: 8px;
            background-color: #0f172a;
            content: '';
            transform: translate(-50%, -4px) rotate(45deg);
        }

        .project-orders-tooltip-wrapper:hover .project-orders-tooltip,
        .project-orders-tooltip-wrapper:focus-within .project-orders-tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    </style>

    <div class="mx-auto max-w-screen-2xl space-y-6">
        <section class="relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-slate-900">Dashboard filters</h2>
                            <span class="text-slate-300">|</span>
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                                Project dashboard
                            </span>
                        </div>
                        <h1 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900">
                            {{ $project->name }}
                        </h1>
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-slate-500">
                            <span class="font-semibold text-slate-700">{{ $project->pda_code }}</span>
                            <span>{{ $project->company?->company_name }}</span>
                            <span class="rounded-full px-2.5 py-1 font-medium"
                                style="background-color: {{ $project->state->softColor() }}; color: {{ $project->state->textColor() }};">
                                {{ $project->state->value }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canExportReport)
                            <x-excel-export-button method="exportReport" />
                        @endif

                        <a href="{{ route('projects.data', ['project' => $project->slug]) }}" wire:navigate
                            class="project-dashboard-action inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-blue-600 bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:-translate-y-0.5 hover:border-blue-500 hover:bg-blue-500 hover:text-white hover:shadow-md active:translate-y-0 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12.5 15 7.5 10l5-5M8 10h8" />
                            </svg>
                            Back to data
                        </a>

                        @if ($hasOrders)
                            <a href="{{ route('projects.orders', ['project' => $project->id]) }}" wire:navigate
                                title="View project orders"
                                class="project-dashboard-action back-to-projects-action inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                                </svg>
                                Orders
                            </a>
                        @else
                            <span class="project-orders-tooltip-wrapper inline-flex" tabindex="0">
                                <button type="button" disabled aria-disabled="true"
                                    class="project-dashboard-action back-to-projects-action project-orders-disabled inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                                    </svg>
                                    Orders
                                </button>
                                <span role="tooltip" class="project-orders-tooltip">
                                    This project has no orders
                                </span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="overflow-x-auto">
                <div class="flex min-w-max items-center gap-3">
                    <x-dashboard-filter-dropdown label="Group by" model="searchData"
                        :options="collect($columnNames)->map(fn ($columnName) => [
                            'value' => $columnName,
                            'label' => $this->formatText($columnName),
                        ])"
                        :selected="$searchData" default="area" />

                    <x-dashboard-filter-dropdown label="Financial value" model="investments"
                        :options="[
                            ['value' => 'global_price_euros', 'label' => 'Budgeted'],
                            ['value' => 'real_value_euros', 'label' => 'Real value (SAP)'],
                            ['value' => 'booked_euros', 'label' => 'Booked'],
                            ['value' => 'executed_euros', 'label' => 'Executed'],
                        ]"
                        :selected="$investments" default="global_price_euros" />

                    <x-dashboard-filter-dropdown label="Currency" model="dollarOrEuro"
                        :options="[
                            ['value' => 'euro', 'label' => 'Euro (€)'],
                            ['value' => 'dollar', 'label' => 'Dollar ($)'],
                        ]"
                        :selected="$dollarOrEuro" default="euro" />

                    <x-clear-filters-button method="resetAll"
                        :active="$searchData !== 'area' || $investments !== 'global_price_euros'" />
                </div>
                </div>

                @php
                    $financialFilterLabels = [
                        'global_price_euros' => 'Budgeted',
                        'real_value_euros' => 'Real value (SAP)',
                        'booked_euros' => 'Booked',
                        'executed_euros' => 'Executed',
                    ];
                @endphp

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                    <span class="mr-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Active filters
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                        <span class="text-blue-400">Group by:</span>
                        {{ $this->formatText($searchData) }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        <span class="text-emerald-500">Value:</span>
                        {{ $financialFilterLabels[$investments] ?? $this->formatText($investments) }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">
                        <span class="text-violet-500">Currency:</span>
                        {{ $dollarOrEuro === 'dollar' ? 'Dollar ($)' : 'Euro (€)' }}
                    </span>
                </div>
            </div>
        </section>

        @php
            $currencySymbol = $dollarOrEuro === 'dollar' ? '$' : '€';
            $metrics = [
                ['label' => 'Budgeted', 'value' => $currencySymbol . ' ' . number_format($budgeted, 2), 'color' => 'blue'],
                ['label' => 'Booked', 'value' => $currencySymbol . ' ' . number_format($booked, 2), 'color' => 'amber'],
                ['label' => 'Progress', 'value' => number_format($percentage, 2) . '%', 'color' => 'cyan'],
                ['label' => 'Executed', 'value' => $currencySymbol . ' ' . number_format($executed, 2), 'color' => 'emerald'],
                ['label' => 'Real (SAP)', 'value' => $currencySymbol . ' ' . number_format($real_value, 2), 'color' => 'violet'],
            ];
        @endphp

        <section class="overflow-x-auto pb-1">
            <div class="gap-4"
                style="display: grid; grid-template-columns: repeat(5, minmax(190px, 1fr)); min-width: 950px;">
                @foreach ($metrics as $metric)
                    <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span @class([
                            'absolute inset-y-0 left-0 w-1',
                            'bg-blue-500' => $metric['color'] === 'blue',
                            'bg-amber-500' => $metric['color'] === 'amber',
                            'bg-cyan-500' => $metric['color'] === 'cyan',
                            'bg-emerald-500' => $metric['color'] === 'emerald',
                            'bg-violet-500' => $metric['color'] === 'violet',
                        ])></span>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'inline-flex h-10 w-10 shrink-0 items-center justify-center',
                                'text-blue-600' => $metric['color'] === 'blue',
                                'text-amber-600' => $metric['color'] === 'amber',
                                'text-cyan-600' => $metric['color'] === 'cyan',
                                'text-emerald-600' => $metric['color'] === 'emerald',
                                'text-violet-600' => $metric['color'] === 'violet',
                            ])>
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    @if ($metric['label'] === 'Progress')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h15M6.75 16.5v-3m5.25 3V9m5.25 7.5V6" />
                                    @elseif ($metric['label'] === 'Executed')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    @elseif ($metric['label'] === 'Real (SAP)')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6c0 1.657-3.358 3-7.5 3S4.5 7.657 4.5 6 7.858 3 12 3s7.5 1.343 7.5 3Zm0 0v12c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3V6" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v11.25A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25V7.5h6V5.25" />
                                    @endif
                                </svg>
                            </span>
                            <p class="whitespace-nowrap text-sm font-medium text-slate-500">{{ $metric['label'] }}</p>
                        </div>
                        <p class="mt-2 whitespace-nowrap text-2xl font-bold tracking-tight text-slate-900">
                            {{ $metric['value'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            @php
                $charts = [
                    ['name' => 'project-classification', 'title' => 'Project classification', 'type' => 'column', 'model' => $columnChartModel],
                    ['name' => 'project-comparison', 'title' => 'Financial comparison', 'type' => 'column', 'model' => $multiColumnChartModel],
                    ['name' => 'project-progress-percentage', 'title' => 'Project progress', 'type' => 'column', 'model' => $resumePercentageGraph],
                    ['name' => 'project-financial-summary', 'title' => 'Financial summary', 'type' => 'column', 'model' => $resumeGraph],
                    ['name' => 'project-distribution', 'title' => 'Investment distribution', 'type' => 'pie', 'model' => $pieChartModel],
                    ['name' => 'project-real-balance', 'title' => 'Balance with real value', 'type' => 'pie', 'model' => $pieChartModelResume],
                    ['name' => 'project-radar', 'title' => 'Investment radar', 'type' => 'radar', 'model' => $radarChartModel],
                    ['name' => 'project-booked-balance', 'title' => 'Balance with booked value', 'type' => 'pie', 'model' => $pieChartModelResumeTwo],
                ];
            @endphp

            @foreach ($charts as $chart)
                <x-dashboard-chart-card :title="$chart['title']"
                    subtitle="Project {{ $project->pda_code }}"
                    :filename="$project->pda_code . '-' . $chart['name']">
                    @if ($chart['type'] === 'column')
                        <livewire:livewire-column-chart
                            key="{{ $chart['model']->reactiveKey() }}"
                            :column-chart-model="$chart['model']" />
                    @elseif ($chart['type'] === 'pie')
                        <livewire:livewire-pie-chart
                            key="{{ $chart['model']->reactiveKey() }}"
                            :pie-chart-model="$chart['model']" />
                    @else
                        <livewire:livewire-radar-chart
                            key="{{ $chart['model']->reactiveKey() }}"
                            :radar-chart-model="$chart['model']" />
                    @endif
                </x-dashboard-chart-card>
            @endforeach
        </section>
    </div>
</div>
