<div x-data="{ open: true }"
    class="relative overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('Project filters') }}
            </h2>
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                {{ __(':count projects', ['count' => number_format($filteredProjectCount)]) }}
            </span>
        </div>

        <button type="button" x-on:click="open = !open"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-gray-600 transition hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
            x-bind:title="open ? '{{ __('Collapse') }}' : '{{ __('Expand') }}'" x-bind:aria-expanded="open">
            <span class="sr-only" x-text="open ? '{{ __('Collapse filters') }}' : '{{ __('Expand filters') }}'"></span>

            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
            </svg>

            <svg x-show="!open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
            </svg>
        </button>
    </div>

    {{-- Contenido colapsable --}}
    <div x-show="open" x-collapse class="overflow-hidden">
        <div class="overflow-x-auto px-4 py-4">
            <div class="flex min-w-max items-center justify-center gap-3">

                {{-- Compañías o plantas --}}
                <x-dashboard-filter-dropdown label="Companies" model="plantFilter"
                    :options="$companies->map(fn ($company) => [
                        'value' => $company->company_code,
                        'label' => $company->company_name,
                    ])"
                    :selected="$plantFilter" multiple />

                {{-- Ordenar por restante --}}
                <div class="shrink-0">
                    <button wire:click="projectOrder" wire:loading.attr="disabled" wire:target="projectOrder"
                        type="button"
                        title="{{ $orderByProject ? 'Return to the default project order' : 'Prioritize projects by remaining budget' }}"
                        class="group inline-flex h-11 min-w-44 items-center justify-between gap-3 rounded-lg border px-3 text-sm font-semibold shadow-sm transition duration-150 hover:-translate-y-px hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60
                            {{ $orderByProject
                                ? 'border-blue-700 bg-blue-600 text-white hover:bg-blue-500 focus:ring-blue-500'
                                : 'border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:ring-blue-500' }}">
                        <span wire:loading.remove wire:target="projectOrder" class="flex items-center gap-2">
                            @if ($orderByProject)
                                <span class="h-2 w-2 rounded-full bg-emerald-300 ring-2 ring-white/30"></span>
                                {{ __('Ordered by remaining') }}
                            @else
                                {{ __('Order by remaining') }}
                            @endif
                        </span>
                        <span wire:loading wire:target="projectOrder">{{ __('Updating...') }}</span>

                        @if ($orderByProject)
                            <svg wire:loading.remove wire:target="projectOrder" class="h-4 w-4"
                                viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                            </svg>
                        @else
                            <svg wire:loading.remove wire:target="projectOrder" class="h-4 w-4 transition group-hover:-translate-y-0.5"
                                viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 14.5 10 9l3 3 3.5-5M13 7h3.5v3.5" />
                            </svg>
                        @endif
                    </button>
                </div>

                {{-- Año --}}
                <x-dashboard-filter-dropdown label="Years" model="yearSearch"
                    :options="collect($years)->map(fn ($year) => [
                        'value' => $year,
                        'label' => $year,
                    ])"
                    :selected="$yearSearch" multiple />

                {{-- Clasificación del proyecto --}}
                <x-dashboard-filter-dropdown label="Classifications" model="typeOfProjectSearch"
                    :options="collect($classificationOptions)->map(fn ($classification) => [
                        'value' => $classification->value,
                        'label' => $classification->value,
                    ])"
                    :selected="$typeOfProjectSearch" multiple />

                {{-- Estado --}}
                <x-dashboard-filter-dropdown label="States" model="stateSearch"
                    :options="collect($stateOptions)->map(fn ($state) => [
                        'value' => $state->value,
                        'label' => $state->value,
                    ])"
                    :selected="$stateSearch" multiple />

                {{-- Inversión --}}
                <x-dashboard-filter-dropdown label="Investments" model="investmentFilter"
                    :options="collect($investmentOptions)->map(fn ($investment) => [
                        'value' => $investment->value,
                        'label' => $investment->value,
                    ])"
                    :selected="$investmentFilter" multiple />

                {{-- Limpiar todos los filtros --}}
                <div class="shrink-0">
                    <x-clear-filters-button method="resetAll"
                        :active="$plantFilter !== [] || $yearSearch !== [] || $stateSearch !== [] || $typeOfProjectSearch !== [] || $investmentFilter !== [] || $orderByProject" />
                </div>
            </div>

        </div>

    </div>
</div>
