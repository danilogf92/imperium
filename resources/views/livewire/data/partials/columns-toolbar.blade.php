<div class="unified-table-toolbar">
            <div>
                <p class="text-sm font-semibold text-slate-700">{{ __('Table columns') }}</p>
                <p class="text-xs text-slate-500">
                    {{ count($visibleColumns) }} of {{ count($columnOptions) }} visible
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div x-show="columnsOpen" x-transition class="flex items-center gap-2">
                    <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
                        ->except('actions')
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />
                    <button wire:click="resetColumns" data-global-loading type="button"
                        class="data-action-button data-default-columns inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Default columns
                    </button>
                </div>
                <button type="button" x-on:click="columnsOpen = !columnsOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="Collapse column controls">
                    <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !columnsOpen }"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>
        </div>
