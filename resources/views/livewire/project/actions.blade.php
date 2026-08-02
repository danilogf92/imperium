<div x-data="{ open: true }" class="relative overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            Project Actions
        </h2>

        {{-- Botón colapsar/desplegar --}}
        <button type="button" x-on:click="open = !open"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-gray-600 transition hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
            x-bind:title="open ? 'Collapse' : 'Expand'">
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
        <div class="overflow-x-auto">
            <div class="flex min-w-[1100px] w-full flex-nowrap items-center gap-4 px-4 py-5">

                {{-- Buscar y limpiar --}}
                <div class="min-w-64 flex-1">
                    <label for="projects-search" class="sr-only">Search projects</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                        <input id="projects-search" wire:model.live.debounce.400ms="search" data-global-loading type="text"
                            placeholder="Search project, PDA or plant" autocomplete="off"
                            class="block h-11 w-full rounded-lg border border-slate-300 bg-white py-2 pl-11 pr-11 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        @if (filled($search))
                            <button wire:click="clearSearch" data-global-loading type="button" title="Clear search" aria-label="Clear search"
                                class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Botones de exportación --}}
                <div class="flex shrink-0 items-center gap-3">
                    @if ($canExportProjects)
                    <button wire:click="exportDashboard" data-no-global-loading wire:loading.attr="disabled"
                        wire:target="exportDashboard" type="button"
                        class="group inline-flex h-11 items-center justify-center gap-2.5 rounded-lg border border-blue-700 bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                        <svg wire:loading.remove wire:target="exportDashboard" class="h-5 w-5"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 19.5h16M6.5 17V11m5 6V6.5m5 10.5V9" />
                        </svg>
                        <svg wire:loading wire:target="exportDashboard" class="h-5 w-5 animate-spin"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportDashboard">Dashboard Excel</span>
                        <span wire:loading wire:target="exportDashboard">Generating...</span>
                    </button>

                    <x-excel-export-button method="export" label="Export projects"
                        loading-label="Generating Excel..." />
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
