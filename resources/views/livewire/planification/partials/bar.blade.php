<div class="flex flex-col gap-2 border-b border-gray-200 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">

    <div class="flex min-w-0 flex-1 items-center gap-3">

        <label for="planification-search" class="sr-only">
            {{ __('Search project or milestone') }}
        </label>

        <div class="relative w-full sm:max-w-lg">

            {{-- Icono de búsqueda --}}
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />
            </svg>

            {{--
                            BUSCADOR

                            data-no-global-loading evita mostrar el spinner global
                            cada vez que el debounce dispara una petición Livewire.
                        --}}
            <input id="planification-search" type="text" wire:model.live.debounce.400ms="search"
                data-no-global-loading placeholder="{{ __('Search project or milestone') }}" autocomplete="off"
                class="block h-10 w-full rounded-lg border border-slate-300 bg-white py-2 pl-11 pr-11 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">

            {{-- Botón para limpiar búsqueda --}}
            @if (filled($search))
                <button type="button" wire:click="clearSearch" data-no-global-loading title="{{ __('Clear search') }}"
                    aria-label="{{ __('Clear search') }}"
                    class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">

                        <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                    </svg>
                </button>
            @endif
        </div>

        <span
            class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
            {{ __(':count projects', ['count' => number_format($plannedProjects->total())]) }}
        </span>
    </div>

    {{-- Acciones de la barra superior --}}
    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:gap-3">

        <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($fixedColumnOptions)
            ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
            ->values()" :selected="$visibleColumns"
            multiple />

        {{-- <button type="button" wire:click="resetColumns" data-no-global-loading
            class="inline-flex h-10 cursor-pointer items-center rounded-lg bg-slate-600 px-3 text-sm font-semibold text-white hover:bg-slate-500">
            Default columns
        </button> --}}

        <x-ui-button :text="__('Default columns')" color="#4B5569" hover-opacity="0.80" text-color="#FFFFFF"
            wire:click="resetColumns" data-no-global-loading />

        @if ($canExport)
            {{-- <x-excel-export-button method="exportExcel" /> --}}

            <x-ui-button icon="excel" color="#60BD84" hover-opacity="0.80" text-color="#FFFFFF"
                wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" data-no-global-loading>
                <span wire:loading.remove wire:target="exportExcel">
                    {{ __('Export Excel') }}
                </span>

                <span wire:loading wire:target="exportExcel">
                    {{ __('Generating...') }}
                </span>
            </x-ui-button>
        @endif

        <x-per-page-select id="planification-per-page" />

        {{-- Abrir modal de creación --}}
        {{-- <button type="button" wire:click="openCreate" data-no-global-loading
            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">

            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>

            {{ __('Add milestone') }}
        </button> --}}

        @if ($canUpdatePlanification)
            <x-ui-button :text="__('Add milestone')" icon="plus" color="#EBB352" hover-opacity="0.80" text-color="#FFFFFF"
                wire:click="openCreate" data-no-global-loading />
        @endif
    </div>
</div>
