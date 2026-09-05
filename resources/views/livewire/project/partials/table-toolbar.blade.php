<div class="unified-table-toolbar">
    <div>
        <p class="text-sm font-semibold text-gray-700">{{ __('Table columns') }}</p>
        <p class="text-xs text-gray-500">
            Showing {{ $projects->firstItem() ?? 0 }}-{{ $projects->lastItem() ?? 0 }} of
            {{ number_format($projects->total()) }} projects · {{ count($visibleColumns) }} of
            {{ count($columnOptions) }} columns visible
        </p>
    </div>

    <div class="ml-auto flex w-full flex-wrap items-center justify-end gap-2 lg:w-auto">
        <div class="w-full sm:w-auto">
            <label for="project-column-view" class="sr-only">{{ __('Saved table views') }}</label>
            <select id="project-column-view" wire:model.live="selectedColumnView"
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
                class="inline-flex h-11 items-center justify-center rounded-lg bg-red-600 px-3 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50">
                {{ __('Delete view') }}
            </button>
        @endif
        <x-action-message on="column-view-deleted" class="text-sm text-emerald-700">{{ __('View deleted.') }}</x-action-message>

        <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
            ->except('actions')
            ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
            ->values()" :selected="$visibleColumns"
            multiple :close-on-select="false" />

        <form wire:submit="saveColumnView" class="flex w-full flex-wrap items-start gap-2 sm:w-auto">
            <div class="min-w-0 flex-1 sm:w-44">
                <label for="project-column-view-name" class="sr-only">{{ __('Table view name') }}</label>
                <input id="project-column-view-name" type="text" wire:model="columnViewName" maxlength="60"
                    placeholder="{{ __('Table view name') }}"
                    class="h-11 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    @error('columnViewName') aria-invalid="true" aria-describedby="project-column-view-error" @enderror>
                @error('columnViewName')
                    <p id="project-column-view-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveColumnView"
                class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                {{ __('Save view') }}
            </button>
        </form>

        <x-action-message on="column-view-saved" class="text-sm text-emerald-700">{{ __('Saved.') }}</x-action-message>

        <button wire:click="resetColumns" type="button"
            class="default-columns-button inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Default columns
        </button>
    </div>
</div>
