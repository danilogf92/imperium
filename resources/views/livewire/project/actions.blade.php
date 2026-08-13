<div x-data="{ open: true }"
    class="module-accent-line relative overflow-hidden rounded-lg border border-slate-200 bg-white shadow-md dark:bg-gray-800">


    {{-- Encabezado --}}
    <div
        class="flex flex-col items-start gap-2 border-b border-b-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-b-gray-700">



        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ __('Project Actions') }}
        </h2>

        {{-- Botón colapsar/desplegar --}}
        <button type="button" x-on:click="open = !open"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-gray-600 transition hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
            x-bind:title="open ? '{{ __('Collapse') }}' : '{{ __('Expand') }}'">
            {{-- Flecha arriba --}}
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
            </svg>

            {{-- Flecha abajo --}}
            <svg x-show="!open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
            </svg>
        </button>
    </div>

    {{-- Contenido colapsable --}}
    <div x-show="open" x-collapse class="overflow-hidden transition-all duration-100 py-2">
        <div>
            <div class="grid w-full grid-cols-1 items-center gap-3 px-4 py-4 sm:grid-cols-2 lg:flex lg:flex-nowrap">

                {{-- Buscar y limpiar --}}
                <div class="min-w-0 flex-1 sm:col-span-2 lg:min-w-64">
                    <label for="projects-search" class="sr-only">{{ __('Search projects') }}</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                        <input id="projects-search" wire:model.live.debounce.400ms="search" data-no-global-loading
                            type="text" placeholder="{{ __('Search project, PDA or plant') }}" autocomplete="off"
                            class="block h-11 w-full rounded-lg border border-slate-300 bg-white py-2 pl-11 pr-11 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        @if (filled($search))
                            <button wire:click="clearSearch" data-global-loading type="button"
                                title="{{ __('Clear search') }}" aria-label="{{ __('Clear search') }}"
                                class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Botones de exportación --}}
                <div class="flex shrink-0 items-center gap-3">
                    @if ($canExportProjects)
                        <x-ui-button icon="chart" color="#7DB9F1" hover-opacity="0.80" text-color="#FFFFFF"
                            wire:click="exportDashboard" wire:loading.attr="disabled" wire:target="exportDashboard"
                            data-no-global-loading>
                            <span wire:loading.remove wire:target="exportDashboard">
                                {{ __('Dashboard Excel') }}
                            </span>

                            <span wire:loading wire:target="exportDashboard">
                                {{ __('Generating...') }}
                            </span>
                        </x-ui-button>

                        <x-ui-button icon="excel" color="#60BD84" hover-opacity="0.80" text-color="#FFFFFF"
                            wire:click="export" wire:loading.attr="disabled" wire:target="export"
                            data-no-global-loading>
                            <span wire:loading.remove wire:target="export">
                                {{ __('Export projects') }}
                            </span>

                            <span wire:loading wire:target="export">
                                {{ __('Generating Excel...') }}
                            </span>
                        </x-ui-button>
                    @endif

                </div>

                {{-- Registros por página --}}
                <x-per-page-select id="projects-per-page" />

                {{-- Crear proyecto --}}
                <div class="flex shrink-0 items-center">
                    <livewire:project.create />
                </div>

            </div>
        </div>
    </div>
</div>
