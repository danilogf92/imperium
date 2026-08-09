<div x-data="{ open: true }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div
            class="flex flex-col items-start gap-3 border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm"
                    style="background-color: #dbeafe; color: #2563eb;">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900">{{ __('Data filters') }}</h2>
                        @if ($hasActiveFilters)
                            <span
                                class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                {{ __('Active') }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">
                        {{ __('Refine the records displayed in this project') }}
                    </p>
                </div>
            </div>
            <button type="button" x-on:click="open = !open"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                </svg>
            </button>
        </div>

        <div x-show="open" x-collapse>
            <div class="flex min-w-0 flex-wrap items-end gap-3 overflow-x-hidden px-4 py-4">
                <div class="relative min-w-0 flex-[1_1_16rem]">
                    <svg class="pointer-events-none absolute h-4 w-4 text-slate-400 transition-colors"
                        style="left: 0.75rem; top: 50%; transform: translateY(-50%);" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input wire:model.live.debounce.400ms="search" data-global-loading type="search"
                        aria-label="{{ __('Search project data') }}" placeholder="{{ __('Search project data...') }}"
                        style="padding-left: 2.75rem;"
                        class="h-11 w-full rounded-lg border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 shadow-sm transition duration-150 placeholder:text-slate-400 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:shadow-md focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/25">
                </div>

                <x-dashboard-filter-dropdown label="Area" model="areaFilter" :options="$filterOptions['areaFilter']" :selected="$areaFilter"
                    multiple />
                <x-dashboard-filter-dropdown label="Classification" model="classificationFilter" :options="$filterOptions['classificationFilter']"
                    :selected="$classificationFilter" multiple />
                <x-dashboard-filter-dropdown label="Item type" model="itemTypeFilter" :options="$filterOptions['itemTypeFilter']"
                    :selected="$itemTypeFilter" multiple />
                <x-dashboard-filter-dropdown label="Stage" model="stageFilter" :options="$filterOptions['stageFilter']" :selected="$stageFilter"
                    multiple />
                <x-dashboard-filter-dropdown label="Supplier" model="supplierFilter" :options="$filterOptions['supplierFilter']"
                    :selected="$supplierFilter" multiple />
                <x-dashboard-filter-dropdown label="Order year" model="orderYearFilter" :options="$filterOptions['orderYearFilter']"
                    :selected="$orderYearFilter" multiple />

                <x-per-page-select id="data-per-page" />

                <x-clear-filters-button method="resetFilters" :active="$hasActiveFilters" />
            </div>
        </div>
    </div>
