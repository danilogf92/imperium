<div class="min-h-screen bg-slate-100 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-screen-2xl space-y-6">
        {{-- <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">
                    Investment analytics
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">
                    Projects dashboard
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Portfolio performance for your assigned companies.
                </p>
            </div>

            <a href="{{ route('projects') }}"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Back to projects
            </a>
        </header> --}}

        <section class="relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Dashboard filters</h2>
            </div>

            <div class="overflow-x-auto p-5">
                <div class="flex min-w-max items-center gap-3">
                    <x-dashboard-filter-dropdown label="Companies" model="companyFilter"
                        :options="$companies->map(fn ($company) => [
                            'value' => $company->company_code,
                            'label' => $company->company_name,
                        ])"
                        :selected="$companyFilter" multiple />

                <div x-data="{ open: false }" class="shrink-0">
                    <button x-ref="trigger" type="button"
                        @click="
                            open = !open;
                            if (open) {
                                $nextTick(() => {
                                    const rect = $refs.trigger.getBoundingClientRect();
                                    $refs.menu.style.left = `${rect.left}px`;
                                    $refs.menu.style.top = `${rect.bottom + 8}px`;
                                });
                            }
                        "
                        :class="open ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700' : 'border-slate-300'"
                        class="inline-flex h-11 w-32 cursor-pointer items-center justify-between rounded-lg border bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        <span>Years</span>
                        <span class="flex items-center gap-2">
                            @if (count($yearSearch) > 0)
                                <span
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-xs font-semibold text-white">
                                    {{ count($yearSearch) }}
                                </span>
                            @endif
                            <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }"
                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </span>
                    </button>

                    <template x-teleport="body">
                        <div x-ref="menu" x-show="open" x-cloak
                            @click.outside="open = false"
                            class="fixed z-[200] w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Select years
                            </p>
                            <div class="space-y-1">
                                @foreach ($years as $year)
                                    <label @class([
                                        'flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm transition duration-150 hover:bg-blue-100',
                                        'bg-blue-50 font-medium text-blue-700' => in_array(
                                            $year,
                                            $yearSearch,
                                            true,
                                        ),
                                        'text-slate-700' => !in_array($year, $yearSearch, true),
                                    ])
                                        onmouseenter="this.style.backgroundColor='#dbeafe'"
                                        onmouseleave="this.style.backgroundColor='{{ in_array($year, $yearSearch, true) ? '#eff6ff' : 'transparent' }}'">
                                        <input wire:model.live="yearSearch" type="checkbox"
                                            value="{{ $year }}"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                        <span>{{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </template>
                </div>

                    <x-dashboard-filter-dropdown label="States" model="stateSearch"
                        :options="collect($stateOptions)->map(fn ($option) => [
                            'value' => $option->value,
                            'label' => $option->value,
                        ])"
                        :selected="$stateSearch" multiple />

                    <x-dashboard-filter-dropdown label="Classifications" model="typeOfProjectSearch"
                        :options="collect($classificationOptions)->map(fn ($option) => [
                            'value' => $option->value,
                            'label' => $option->value,
                        ])"
                        :selected="$typeOfProjectSearch" multiple />

                    <x-dashboard-filter-dropdown label="Investments" model="investmentSearch"
                        :options="collect($investmentOptions)->map(fn ($option) => [
                            'value' => $option->value,
                            'label' => $option->value,
                        ])"
                        :selected="$investmentSearch" multiple />

                    <x-dashboard-filter-dropdown label="Justifications" model="justificationSearch"
                        :options="collect($justificationOptions)->map(fn ($option) => [
                            'value' => $option->value,
                            'label' => $option->value,
                        ])"
                        :selected="$justificationSearch" multiple />

                    <x-dashboard-filter-dropdown label="Currency" model="currency"
                        :options="[
                            ['value' => 'euro', 'label' => 'Euro'],
                            ['value' => 'dollar', 'label' => 'Dollar ($)'],
                        ]"
                        :selected="$currency" default="euro" />

                    @if ($currency === 'dollar')
                        <label class="flex h-11 items-center gap-2 whitespace-nowrap text-sm text-slate-600">
                            EUR/USD
                            <input wire:model.blur="exchangeRate" type="number" min="0.01"
                                step="0.01"
                                class="h-11 w-24 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                    @endif

                    <x-clear-filters-button method="resetAll"
                        :active="$companyFilter !== [] || $yearSearch !== [] || $stateSearch !== [] || $typeOfProjectSearch !== [] || $investmentSearch !== [] || $justificationSearch !== [] || $currency !== 'euro' || (float) $exchangeRate !== 1.0" />
                </div>
            </div>
        </section>

        @php
            $currencySymbol = $currency === 'dollar' ? '$' : '€';
            $metrics = [
                ['label' => 'Projects', 'value' => number_format($projectCount), 'accent' => 'bg-indigo-500'],
                [
                    'label' => 'With financial data',
                    'value' => number_format($projectsWithData),
                    'accent' => 'bg-cyan-500',
                ],
                [
                    'label' => 'Budgeted',
                    'value' => $currencySymbol . ' ' . number_format($budgeted, 2),
                    'accent' => 'bg-blue-500',
                ],
                [
                    'label' => 'Booked',
                    'value' => $currencySymbol . ' ' . number_format($booked, 2),
                    'accent' => 'bg-amber-500',
                ],
                [
                    'label' => 'Executed',
                    'value' => $currencySymbol . ' ' . number_format($executed, 2),
                    'accent' => 'bg-emerald-500',
                ],
                [
                    'label' => 'Real (SAP)',
                    'value' => $currencySymbol . ' ' . number_format($realValue, 2),
                    'accent' => 'bg-violet-500',
                ],
            ];
        @endphp

        <section class="overflow-x-auto pb-1">
            <div class="gap-4"
                style="display: grid; grid-template-columns: repeat(6, minmax(190px, 1fr)); min-width: 1140px;">
                @foreach ($metrics as $metric)
                    <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="absolute inset-y-0 left-0 w-1 {{ $metric['accent'] }}"></span>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'inline-flex h-10 w-10 shrink-0 items-center justify-center',
                                'text-indigo-600' => $metric['label'] === 'Projects',
                                'text-cyan-600' => $metric['label'] === 'With financial data',
                                'text-blue-600' => $metric['label'] === 'Budgeted',
                                'text-amber-600' => $metric['label'] === 'Booked',
                                'text-emerald-600' => $metric['label'] === 'Executed',
                                'text-violet-600' => $metric['label'] === 'Real (SAP)',
                            ])>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor" aria-hidden="true">
                                    @switch($metric['label'])
                                        @case('Projects')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 9.75h16.5m-15-4.5h4.5l1.5 1.5h7.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Z" />
                                        @break
                                        @case('With financial data')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 19.5h15M6.75 16.5v-3m5.25 3V9m5.25 7.5V6M5.25 4.5h13.5a.75.75 0 0 1 .75.75v13.5H4.5V5.25a.75.75 0 0 1 .75-.75Z" />
                                        @break
                                        @case('Budgeted')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.5 7.5V6a3 3 0 0 0-3-3h-6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h9a3 3 0 0 0 3-3v-1.5m-9-9h9A1.5 1.5 0 0 1 21 9v6a1.5 1.5 0 0 1-1.5 1.5h-9a3 3 0 0 1 0-6Zm6 4.5h.008v.008H16.5V12Z" />
                                        @break
                                        @case('Booked')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 5.25H6.75A2.25 2.25 0 0 0 4.5 7.5v11.25A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5a2.25 2.25 0 0 0-2.25-2.25H15M9 5.25A3 3 0 0 1 12 3a3 3 0 0 1 3 2.25M9 5.25V7.5h6V5.25m-6 7.5 2.25 2.25L15 10.5" />
                                        @break
                                        @case('Executed')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m4.5 12.75 6 6 9-13.5" />
                                        @break
                                        @case('Real (SAP)')
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 6c0 1.657-3.358 3-7.5 3S4.5 7.657 4.5 6 7.858 3 12 3s7.5 1.343 7.5 3Zm0 0v6c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3V6m15 6v6c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3v-6" />
                                        @break
                                    @endswitch
                                </svg>
                            </span>
                            <p class="whitespace-nowrap text-sm font-medium text-slate-500">
                                {{ $metric['label'] }}
                            </p>
                        </div>
                        <p class="mt-2 whitespace-nowrap text-2xl font-bold tracking-tight text-slate-900">
                            {{ $metric['value'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        @if ($hasProjects)
            <section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <x-dashboard-chart-card title="Projects by investment"
                    subtitle="Number of projects by investment category"
                    filename="projects-by-investment">
                    <livewire:livewire-column-chart key="{{ $projectsByInvestmentChart->reactiveKey() }}"
                        :column-chart-model="$projectsByInvestmentChart" />
                </x-dashboard-chart-card>

                @if ($hasFinancialData)
                    <x-dashboard-chart-card title="Budget by investment"
                        subtitle="Financial distribution by investment category"
                        filename="budget-by-investment">
                        <livewire:livewire-column-chart key="{{ $budgetByInvestmentChart->reactiveKey() }}"
                            :column-chart-model="$budgetByInvestmentChart" />
                    </x-dashboard-chart-card>
                @endif
            </section>

            <section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <x-dashboard-chart-card title="Projects by state"
                    subtitle="Current portfolio status distribution"
                    filename="projects-by-state">
                    <livewire:livewire-pie-chart key="{{ $projectsByStateChart->reactiveKey() }}" :pie-chart-model="$projectsByStateChart" />
                </x-dashboard-chart-card>

                @if ($hasFinancialData)
                    <x-dashboard-chart-card title="Budget by state"
                        subtitle="Budget allocation across project states"
                        filename="budget-by-state">
                        <livewire:livewire-pie-chart key="{{ $budgetByStateChart->reactiveKey() }}"
                            :pie-chart-model="$budgetByStateChart" />
                    </x-dashboard-chart-card>
                @endif
            </section>

            <section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <x-dashboard-chart-card title="Project status count"
                    subtitle="Number of projects in each state"
                    filename="project-status-count">
                    <livewire:livewire-column-chart key="{{ $projectsByStateColumnChart->reactiveKey() }}"
                        :column-chart-model="$projectsByStateColumnChart" />
                </x-dashboard-chart-card>

                @if ($hasFinancialData)
                    <x-dashboard-chart-card title="Project status value"
                        subtitle="Financial value grouped by project state"
                        filename="project-status-value">
                        <livewire:livewire-column-chart key="{{ $budgetByStateColumnChart->reactiveKey() }}"
                            :column-chart-model="$budgetByStateColumnChart" />
                    </x-dashboard-chart-card>
                @endif
            </section>

            @if ($hasFinancialData)
                <section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <x-dashboard-chart-card title="Investment type"
                        subtitle="Investment value by classification"
                        filename="investment-type">
                        <livewire:livewire-radar-chart key="{{ $budgetByInvestmentRadarChart->reactiveKey() }}"
                            :radar-chart-model="$budgetByInvestmentRadarChart" />
                    </x-dashboard-chart-card>

                    @if ($hasAreaData)
                        <x-dashboard-chart-card title="Area classification"
                            subtitle="Investment distribution across project areas"
                            filename="area-classification">
                            <livewire:livewire-radar-chart key="{{ $budgetByAreaRadarChart->reactiveKey() }}"
                                :radar-chart-model="$budgetByAreaRadarChart" />
                        </x-dashboard-chart-card>
                    @endif
                </section>

                <section class="grid gap-6" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <x-dashboard-chart-card title="Cumulative projects by month"
                        subtitle="Portfolio growth throughout the year"
                        filename="cumulative-projects-by-month">
                        <livewire:livewire-line-chart key="{{ $projectsCreationCurveChart->reactiveKey() }}"
                            :line-chart-model="$projectsCreationCurveChart" />
                    </x-dashboard-chart-card>

                    <x-dashboard-chart-card title="Cumulative budget by month"
                        subtitle="Budget growth by project creation month"
                        filename="cumulative-budget-by-month">
                        <livewire:livewire-line-chart key="{{ $budgetCreationCurveChart->reactiveKey() }}"
                            :line-chart-model="$budgetCreationCurveChart" />
                    </x-dashboard-chart-card>
                </section>
            @else
                <section
                    class="flex min-h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <div>
                        <h2 class="font-semibold text-slate-800">No financial data available</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Financial charts will appear when project data is uploaded.
                        </p>
                    </div>
                </section>
            @endif
        @else
            <section
                class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                No projects are available for the selected filters.
            </section>
        @endif

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-dashboard-chart-card title="Start date vs approved date"
                subtitle="Cumulative planned and actual project execution"
                filename="planned-vs-actual-execution"
                height="40rem">
                <livewire:livewire-line-chart key="{{ $plannedVsActualExecutionChart->reactiveKey() }}"
                    :line-chart-model="$plannedVsActualExecutionChart" />
                <x-slot:footer>
                    Porcentaje acumulado sobre un valor real total de
                    {{ $currencySymbol }}{{ number_format($scheduleRealValueTotal, 2) }}.
                </x-slot:footer>
            </x-dashboard-chart-card>

            <x-dashboard-chart-card title="Forecast end date vs close date"
                subtitle="Monthly project completion comparison"
                filename="forecast-vs-close-date"
                height="40rem">
                <livewire:livewire-line-chart key="{{ $forecastVsCloseDateChart->reactiveKey() }}"
                    :line-chart-model="$forecastVsCloseDateChart" />
                <x-slot:footer>
                    Cantidad mensual de proyectos por fecha prevista de finalización y fecha de cierre.
                </x-slot:footer>
            </x-dashboard-chart-card>
        </section>
    </div>

    <script>
        window.downloadDashboardChart = async function(button) {
            const card = button.closest('[data-chart-card]');
            const chartRoot = card?.querySelector('[x-data]');
            const filename = button.dataset.downloadFilename || 'dashboard-chart';
            const chart = chartRoot && window.Alpine ?
                Alpine.$data(chartRoot).chart :
                null;

            if (!chart || typeof chart.dataURI !== 'function') {
                window.alert('La gráfica todavía no está lista. Inténtalo nuevamente.');
                return;
            }

            button.disabled = true;
            button.style.opacity = '0.5';

            try {
                const image = await chart.dataURI({
                    scale: 2
                });
                const link = document.createElement('a');

                link.download = filename + '.png';
                link.href = image.imgURI;
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('No se pudo descargar la gráfica.', error);
                window.alert('No se pudo descargar la gráfica. Inténtalo nuevamente.');
            } finally {
                button.disabled = false;
                button.style.opacity = '1';
            }
        };
    </script>
</div>
