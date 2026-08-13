<header x-data="{ open: true }"
    class="module-accent-line overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div
        class="soft-title-surface flex flex-col items-start gap-3 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div>
            <h2 class="text-sm font-bold text-slate-900">{{ __('Project data actions') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Project details and available actions') }}</p>
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
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-bold tracking-tight text-slate-900">
                    {{ Str::limit($project->name, 65) }}
                </h1>
                <div class="flex flex-wrap items-center text-sm text-slate-600"
                    style="margin-top: 0.85rem; gap: 0.65rem;">
                    <span
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-800">
                        {{ $project->pda_code }}
                    </span>
                    <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 font-medium text-slate-600">
                        {{ $project->company?->company_name }}
                    </span>
                    @if ($project->state)
                        <span class="rounded-full px-2.5 py-1 font-medium"
                            style="background-color: {{ $project->state->softColor() }}; color: {{ $project->state->textColor() }};">
                            {{ $project->state->value }}
                        </span>
                    @endif
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ __(':count records', ['count' => number_format($data->total())]) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($canExportData)
                    {{-- <x-excel-export-button method="exportImportReadyExcel" label="Export for import"
                        loading-label="Preparing import file..." /> --}}
                    {{-- <x-excel-export-button method="exportExcel" label="Export data" /> --}}

                    <x-ui-button icon="excel" color="#60BD84" hover-opacity="0.80" text-color="#FFFFFF"
                        wire:click="exportImportReadyExcel" wire:loading.attr="disabled"
                        wire:target="exportImportReadyExcel" data-no-global-loading>
                        <span wire:loading.remove wire:target="exportImportReadyExcel">
                            {{ __('Export for import') }}
                        </span>

                        <span wire:loading wire:target="exportImportReadyExcel">
                            {{ __('Preparing import file...') }}
                        </span>
                    </x-ui-button>

                    <x-ui-button icon="excel" color="#60BD84" hover-opacity="0.80" text-color="#FFFFFF"
                        wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                        data-no-global-loading>
                        <span wire:loading.remove wire:target="exportExcel">
                            {{ __('Export data') }}
                        </span>

                        <span wire:loading wire:target="exportExcel">
                            {{ __('Generating Excel...') }}
                        </span>
                    </x-ui-button>
                @endif
                @if ($canEditData)
                    {{-- <button wire:click="openCreateModal" data-no-global-loading type="button"
                        class="data-action-button inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2"
                        style="background-color: #7c3aed;" onmouseenter="this.style.backgroundColor='#6d28d9'"
                        onmouseleave="this.style.backgroundColor='#7c3aed'">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                        </svg>
                        New row
                    </button> --}}


                    <x-ui-button :text="__('New row')" icon="plus" color="#7D7D7D" hover-opacity="0.80"
                        text-color="#FFFFFF" wire:click="openCreateModal" data-no-global-loading />
                @endif
                {{-- <a href="{{ route('projects.dashboard', ['project' => $project->slug]) }}" wire:navigate
                    class="data-action-button inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.5 16.5h13M5.5 14V9.5m4.5 4.5V5.5m4.5 8.5V7.5" />
                    </svg>
                    Dashboard
                </a> --}}

                <x-ui-button :href="route('projects.dashboard', ['project' => $project->slug])" :text="__('Dashboard')" icon="chart" color="#7DB9F1" hover-opacity="0.80"
                    text-color="#FFFFFF" wire:navigate />


                @if ($hasOrders)
                    <a href="{{ route('projects.orders', ['project' => $project->slug]) }}" wire:navigate
                        title="View project orders"
                        class="data-action-button data-back-to-projects inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                        </svg>
                        Orders
                    </a>
                @else
                    <span class="data-orders-tooltip-wrapper inline-flex" tabindex="0"
                        aria-describedby="orders-disabled-tooltip">
                        <button type="button" disabled aria-disabled="true"
                            class="data-action-button data-back-to-projects data-orders-disabled inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                            </svg>
                            Orders
                        </button>
                        <span id="orders-disabled-tooltip" role="tooltip" class="data-orders-tooltip">
                            This project has no orders
                        </span>
                    </span>
                @endif
            </div>
        </div>
    </div>
</header>
