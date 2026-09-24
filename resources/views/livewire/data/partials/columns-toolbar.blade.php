<div class="unified-table-toolbar">
            <div>
                <p class="text-sm font-semibold text-slate-700">{{ __('Table columns') }}</p>
                <p class="text-xs text-slate-500">
                    {{ __(':shown of :total visible', ['shown' => count($visibleColumns), 'total' => count($columnOptions)]) }}
                </p>
            </div>
            <div class="ml-auto flex w-full flex-wrap items-center justify-end gap-2 lg:w-auto">
                <div class="w-full sm:w-auto">
                    <label for="data-column-view" class="sr-only">{{ __('Saved table views') }}</label>
                    <select id="data-column-view" wire:model.live="selectedColumnView"
                        class="h-11 w-full rounded-lg border-gray-300 text-sm text-gray-700 focus:border-blue-500 focus:ring-blue-500 sm:w-48">
                        <option value="">{{ __('Saved table views') }}</option>
                        @foreach ($this->savedColumnViews as $viewId => $columnView)
                            <option value="{{ $viewId }}">{{ $columnView['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($selectedColumnView !== '')
                    <button type="button" wire:click="deleteColumnView" wire:loading.attr="disabled"
                        wire:target="deleteColumnView,selectedColumnView"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-red-600 px-3 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ __('Delete view') }}
                    </button>
                @endif
                <x-action-message on="column-view-deleted" class="text-sm text-emerald-700">{{ __('View deleted.') }}</x-action-message>
                <div x-show="columnsOpen" x-transition class="flex flex-wrap items-center gap-2">
                    <x-dashboard-filter-dropdown label="{{ __('Columns') }}" model="visibleColumns" :options="collect($columnOptions)
                        ->except('actions')
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />
                    <button wire:click="resetColumns" data-global-loading type="button"
                        class="data-action-button data-default-columns inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Default columns') }}
                    </button>
                </div>
                <form wire:submit="saveColumnView" class="flex w-full flex-wrap items-start gap-2 sm:w-auto">
                    <div class="min-w-0 flex-1 sm:w-44">
                        <label for="data-column-view-name" class="sr-only">{{ __('Table view name') }}</label>
                        <input id="data-column-view-name" type="text" wire:model="columnViewName" maxlength="60"
                            placeholder="{{ __('Table view name') }}"
                            class="h-11 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            @error('columnViewName') aria-invalid="true" aria-describedby="data-column-view-error" @enderror>
                        @error('columnViewName')
                            <p id="data-column-view-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveColumnView"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        {{ __('Save view') }}
                    </button>
                </form>
                <x-action-message on="column-view-saved" class="text-sm text-emerald-700">{{ __('Saved.') }}</x-action-message>
                <button type="button" x-on:click="columnsOpen = !columnsOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="{{ __('Collapse column controls') }}">
                    <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !columnsOpen }"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>
        </div>
