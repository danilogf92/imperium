            <div class="unified-table-toolbar">
                <div>
                    <p class="text-sm font-semibold text-gray-700">{{ __('Table columns') }}</p>
                    <p class="text-xs text-gray-500">
                        Showing {{ $projects->firstItem() ?? 0 }}-{{ $projects->lastItem() ?? 0 }} of
                        {{ number_format($projects->total()) }} projects · {{ count($visibleColumns) }} of
                        {{ count($columnOptions) }} columns visible
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
                        ->except('actions')
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />

                    <button wire:click="resetColumns" type="button"
                        class="default-columns-button inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Default columns
                    </button>
                </div>
            </div>
