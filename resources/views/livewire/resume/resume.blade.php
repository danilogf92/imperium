<div class="dashboard-page-shell">
    <x-unified-table-theme />
    <div class="dashboard-page-content space-y-6">
        <header class="module-accent-line rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                        {{ __('Portfolio analytics') }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('Project resume') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('Executive financial summary by project forecast start year.') }}
                    </p>
                </div>

                @if ($canExport)
                    {{-- <x-excel-export-button method="exportExcel" label="Export resume" /> --}}

                    <x-ui-button compact-mobile icon="excel" color="#60BD84" hover-opacity="0.80" text-color="#FFFFFF"
                        wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                        data-no-global-loading>
                        <span wire:loading.remove wire:target="exportExcel">
                            {{ __('Export resume') }}
                        </span>

                        <span wire:loading wire:target="exportExcel">
                            {{ __('Generating Excel...') }}
                        </span>
                    </x-ui-button>
                @endif
            </div>
        </header>


        <section x-data="{ open: window.matchMedia('(min-width: 768px)').matches }" data-compact-filters x-on:resize.window.debounce.150ms="if (window.innerWidth >= 768) open = true" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div
                class="soft-title-surface flex flex-col items-start gap-3 border-b px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-semibold text-slate-900">{{ __('Resume filters') }}</h2>
                        @if ($hasActiveFilters)
                            <span
                                class="rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                {{ __('Active') }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">
                        {{ __('Filter the projects included in the annual totals.') }}</p>
                </div>
                <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" aria-label="{{ __('Filters') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>

            <div x-show="open" x-cloak x-collapse>
                <div class="flex flex-wrap items-center gap-2 px-4 py-4 sm:px-5 sm:py-5">
                    <div class="relative w-full shrink-0 sm:w-52">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m16 16 4 4" />
                        </svg>
                        <input wire:model.live.debounce.400ms="search" data-no-global-loading type="text"
                            placeholder="{{ __('Search project or PDA code...') }}"
                            class="h-11 w-full rounded-lg border-slate-300 bg-white pl-10 pr-10 text-sm text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/25">
                        @if ($search !== '')
                            <button wire:click="$set('search', '')" data-no-global-loading type="button"
                                class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                aria-label="{{ __('Clear search') }}">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    <x-dashboard-filter-dropdown label="{{ __('Plants') }}" model="plantFilter" :options="$companies->map(
                        fn($company) => [
                            'value' => (string) $company->id,
                            'label' => $company->company_name,
                        ],
                    )" :selected="$plantFilter"
                        multiple compact :global-loading="false" />

                    <x-dashboard-filter-dropdown label="{{ __('Years') }}" model="yearFilter" :options="$years->map(fn($year) => ['value' => $year, 'label' => $year])" :selected="$yearFilter"
                        multiple compact :global-loading="false" />

                    <x-dashboard-filter-dropdown label="{{ __('Status') }}" model="stateFilter" :options="collect($stateOptions)->map(
                        fn($state) => [
                            'value' => $state->value,
                            'label' => $state->getLabel(),
                        ],
                    )" :selected="$stateFilter"
                        multiple compact :global-loading="false" />

                    <x-dashboard-filter-dropdown label="{{ __('Investments') }}" model="investmentFilter" :options="collect($investmentOptions)->map(
                        fn($investment) => [
                            'value' => $investment->value,
                            'label' => $investment->getLabel(),
                        ],
                    )"
                        :selected="$investmentFilter" multiple compact :global-loading="false" />

                    <x-dashboard-filter-dropdown label="{{ __('Classifications') }}" model="classificationFilter" :options="collect($classificationOptions)->map(
                        fn($classification) => [
                            'value' => $classification->value,
                            'label' => $classification->getLabel(),
                        ],
                    )"
                        :selected="$classificationFilter" multiple compact :global-loading="false" />

                    <x-dashboard-filter-dropdown label="{{ __('Justifications') }}" model="justificationFilter" :options="collect($justificationOptions)->map(
                        fn($justification) => [
                            'value' => $justification->value,
                            'label' => $justification->getLabel(),
                        ],
                    )"
                        :selected="$justificationFilter" multiple compact :global-loading="false" />

                    <div class="w-24 shrink-0">
                        <label for="resume-currency" class="sr-only">{{ __('Currency') }}</label>
                        <select id="resume-currency" wire:model.live="currency" data-no-global-loading
                            class="h-11 w-full cursor-pointer rounded-lg border-slate-300 bg-white text-sm font-medium text-slate-700 shadow-sm hover:border-blue-400 focus:border-blue-500 focus:ring-blue-500">
                            <option value="euro">EUR</option>
                            <option value="dollar">USD</option>
                        </select>
                    </div>

                    <x-clear-filters-button method="clearFilters" :active="$hasActiveFilters" :global-loading="false" />
                </div>
            </div>

<x-filter-chips clear="clearFilters" :filters="[['model' => 'search', 'label' => __('activity_control.search'), 'value' => $search],['model' => 'plantFilter', 'label' => __('Plants'), 'value' => $plantFilter],['model' => 'yearFilter', 'label' => __('Years'), 'value' => $yearFilter],['model' => 'stateFilter', 'translate' => true, 'label' => __('Status'), 'value' => $stateFilter],['model' => 'investmentFilter', 'translate' => true, 'label' => __('Investments'), 'value' => $investmentFilter],['model' => 'classificationFilter', 'translate' => true, 'label' => __('Classification'), 'value' => $classificationFilter],['model' => 'justificationFilter', 'translate' => true, 'label' => __('Justifications'), 'value' => $justificationFilter],['model' => 'currency', 'translate' => true, 'label' => __('Currency'), 'value' => $currency, 'default' => 'euro'] ]" />
</section>

        @php
            $totals = [
                'projects' => $rows->sum('project_count'),
                'budgeted' => $rows->sum('budgeted'),
                'approved' => $rows->sum('approved'),
                'booked' => $rows->sum('booked'),
                'committed' => $rows->sum('committed'),
                'available' => $rows->sum('available'),
            ];
        @endphp

        <section class="overflow-x-auto pb-1">
            <div class="dashboard-metrics-grid gap-4"
                data-summary-metrics>
                @foreach ([['label' => __('Projects'), 'value' => $totals['projects'], 'money' => false, 'accent' => 'bg-indigo-500', 'text' => 'text-indigo-600'], ['label' => __('Budgeted'), 'value' => $totals['budgeted'], 'money' => true, 'accent' => 'bg-sky-500', 'text' => 'text-sky-600'], ['label' => __('Approved'), 'value' => $totals['approved'], 'money' => true, 'accent' => 'bg-blue-500', 'text' => 'text-blue-600'], ['label' => __('Booked (Real SAP)'), 'value' => $totals['booked'], 'money' => true, 'accent' => 'bg-amber-500', 'text' => 'text-amber-600'], ['label' => __('Committed'), 'value' => $totals['committed'], 'money' => true, 'accent' => 'bg-orange-500', 'text' => 'text-orange-600'], ['label' => __('Available'), 'value' => $totals['available'], 'money' => true, 'accent' => $totals['available'] < 0 ? 'bg-red-500' : 'bg-emerald-500', 'text' => $totals['available'] < 0 ? 'text-red-600' : 'text-emerald-600']] as $metric)
                    <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="{{ $metric['accent'] }} absolute inset-y-0 left-0 w-1"></span>
                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $metric['text'] }} inline-flex h-10 w-10 shrink-0 items-center justify-center">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    @if ($metric['label'] === 'Projects')
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 9.75h16.5m-15-4.5h4.5l1.5 1.5h7.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6s-3 1.007-3 2.25 1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25M4.5 19.5h15" />
                                    @endif
                                </svg>
                            </span>
                            <p class="text-sm font-medium text-slate-500">{{ __($metric['label']) }}
                            </p>
                        </div>
                        <p class="mt-2 whitespace-nowrap text-2xl font-bold tracking-tight text-slate-900">
                            @if ($metric['money'])
                                {{ \App\Support\MoneyValueFormatter::thousands($metric['value'], $currencySymbol) }}
                            @else
                                {{ number_format($metric['value']) }}
                            @endif
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="soft-title-surface flex items-center justify-between border-b px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">{{ __('Annual detail') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Financial values backing the stacked chart.') }}
                    </p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ trans_choice('ui.years', $rows->count(), ['count' => $rows->count()]) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="unified-data-table cursor-pointer">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left">{{ __('cash_flow_projection.year') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Number of projects') }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Budgeted') }} {{ $currencySymbol }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Approved') }} {{ $currencySymbol }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Booked (Real SAP)') }} {{ $currencySymbol }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Committed') }} {{ $currencySymbol }}</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right">{{ __('Available') }} {{ $currencySymbol }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rows as $row)
                            <tr class="transition hover:bg-blue-50/50">
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900">{{ $row['year'] }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-indigo-700">
                                    {{ number_format($row['project_count']) }}
                                </td>
                                @foreach (['budgeted', 'approved', 'booked', 'committed'] as $field)
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-700">
                                        <x-compact-money :value="$row[$field]" :symbol="$currencySymbol" />
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-4 py-3 text-right font-bold {{ $row['available'] < 0 ? 'text-red-600' : 'text-violet-600' }}">
                                    <x-compact-money :value="$row['available']" :symbol="$currencySymbol" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center">
                                    <p class="font-semibold text-slate-700">
                                        {{ __('No projects match the selected filters.') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('Clear or change the filters to view the resume.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($rows->isNotEmpty())
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-dashboard-chart-card title="{{ __('Stacked financial position') }}"
                    subtitle="{{ __('Years on X axis and financial values on Y axis') }}"
                    filename="annual-stacked-financial-position" height="34rem">
                    <livewire:livewire-column-chart key="{{ $stackedChart->reactiveKey() }}" :column-chart-model="$stackedChart" />
                    <x-slot:footer>
                        {{ __('Committed = Assigned − Booked (Real SAP). Available = Approved − Assigned.') }}
                    </x-slot:footer>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="{{ __('Financial value comparison') }}"
                    subtitle="{{ __('Financial evolution shown as a smooth area chart') }}" filename="annual-financial-comparison"
                    height="34rem">
                    <x-dashboard-apex-chart :options="$comparisonChartOptions"
                        chart-key="resume-comparison-{{ md5(json_encode($comparisonChartOptions)) }}" />
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="{{ __('Projects by year') }}"
                    subtitle="{{ __('Project columns with Budgeted, Assigned and Executed value lines') }}"
                    filename="annual-project-count" height="34rem">
                    <x-dashboard-apex-chart :options="$projectsChartOptions"
                        chart-key="resume-projects-{{ md5(json_encode($projectsChartOptions)) }}" />
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="{{ __('Available value trend') }}"
                    subtitle="{{ __('Budgeted, Approved, Booked (Real SAP), Committed and Available values by year') }}"
                    filename="annual-available-trend" height="34rem">
                    <livewire:livewire-line-chart key="{{ $availableChart->reactiveKey() }}" :line-chart-model="$availableChart" />
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="{{ __('Financial coverage ratios') }}"
                    subtitle="{{ __('Financial coverage by approval year, with a 100% target') }}"
                    filename="annual-financial-coverage" height="34rem">
                    <x-dashboard-apex-chart :options="$coverageChartOptions"
                        chart-key="resume-coverage-{{ md5(json_encode($coverageChartOptions)) }}" />
                    <x-slot:footer>
                        {{ __('Approved / Budgeted measures budget approval. Booked (Real SAP), Committed and Available are shown as percentages of Approved.') }}
                    </x-slot:footer>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="{{ __('Average value per project') }}"
                    subtitle="{{ __('Average Budgeted, Approved, Booked (Real SAP), Committed and Available per project') }}"
                    filename="annual-average-project-value" height="34rem">
                    <x-dashboard-apex-chart :options="$averageChartOptions"
                        chart-key="resume-average-{{ md5(json_encode($averageChartOptions)) }}" />
                </x-dashboard-chart-card>

                @include('livewire.resume.partials.annual-projection')
            </section>
        @endif

    </div>
</div>
