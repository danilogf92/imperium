<section class="module-accent-line dashboard-panel" id="activities-filters" aria-labelledby="activities-filters-title" data-compact-filters x-on:resize.window.debounce.150ms="if (window.innerWidth >= 768) open = true" x-data="{ open: window.matchMedia('(min-width: 768px)').matches }" x-on:activities-user-selected.window="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-3">
        <h2 id="activities-filters-title" class="font-semibold text-slate-900">{{ __('Activities filters') }}</h2><x-filter-toggle />
        <button type="button" wire:click="resetAll" @disabled(count($activeFilters) === 0)
            class="rounded-lg px-3 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50 disabled:opacity-40">
            {{ __('activity_control.clear_filters') }}
        </button>
    </div>
    <div class="space-y-3 p-3">
        <div x-show="open" x-cloak class="space-y-3">
        <div class="grid items-start gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,2.4fr)_minmax(9rem,0.9fr)_minmax(9rem,0.9fr)]">
            <label class="min-w-0 text-sm font-semibold text-slate-700">{{ __('activity_control.user') }}
                <select wire:model.live="userFilter" class="mt-1 block h-11 w-full min-w-0 rounded-lg border-sky-200 text-sm focus:border-sky-400 focus:ring-sky-200">
                    <option value="">{{ __('activity_control.all_users') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">{{ __('activity_control.status') }}
                <select wire:model.live="status" class="mt-1 block h-11 w-full min-w-0 rounded-lg border-sky-200 text-sm focus:border-sky-400 focus:ring-sky-200">
                    @foreach (['all', 'overdue', 'pending', 'completed'] as $option)
                        <option value="{{ $option }}">{{ __('activity_control.'.$option) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="min-w-0 text-sm font-semibold text-slate-700 sm:col-span-2 lg:col-span-1">{{ __('activity_control.search') }}
                <input type="search" wire:model.live.debounce.350ms="search"
                    placeholder="{{ __('activity_control.search_hint') }}" class="mt-1 block h-11 w-full min-w-0 rounded-lg border-sky-200 text-sm focus:border-sky-400 focus:ring-sky-200">
            </label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">{{ __('activity_control.due_from') }}
                <input type="date" wire:model.live="dateFrom" class="mt-1 block h-11 w-full min-w-0 rounded-lg border-sky-200 bg-slate-50 px-3 text-sm tabular-nums text-slate-700 shadow-sm transition focus:border-sky-400 focus:bg-white focus:ring-sky-200">
                @error('dateFrom') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
            </label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">{{ __('activity_control.due_to') }}
                <input type="date" wire:model.live="dateTo" class="mt-1 block h-11 w-full min-w-0 rounded-lg border-sky-200 bg-slate-50 px-3 text-sm tabular-nums text-slate-700 shadow-sm transition focus:border-sky-400 focus:bg-white focus:ring-sky-200">
                @error('dateTo') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
            </label>
        </div>
        <div class="dashboard-filter-controls grid grid-cols-1 min-[375px]:grid-cols-2 items-end gap-2 sm:flex sm:flex-wrap sm:items-center">
            <x-dashboard-filter-dropdown
                label="Companies"
                model="companyFilter"
                :options="$companies->map(
                    fn ($company) => [
                        'value' => $company->company_code,
                        'label' => $company->company_name,
                    ],
                )"
                :selected="$companyFilter"
                multiple compact
            />

            <x-dashboard-filter-dropdown
                label="Years"
                model="yearSearch"
                :options="collect($years)->map(fn ($year) => ['value' => $year, 'label' => $year])"
                :selected="$yearSearch"
                multiple compact
            />

            <x-dashboard-filter-dropdown
                label="States"
                model="stateSearch"
                :options="collect($stateOptions)->map(
                    fn ($option) => [
                        'value' => $option->value,
                        'label' => $option->value,
                    ],
                )"
                :selected="$stateSearch"
                multiple compact
            />

            <x-dashboard-filter-dropdown
                label="Classifications"
                model="typeOfProjectSearch"
                :options="collect($classificationOptions)->map(
                    fn ($option) => [
                        'value' => $option->value,
                        'label' => $option->value,
                    ],
                )"
                :selected="$typeOfProjectSearch"
                multiple compact
            />

            <x-dashboard-filter-dropdown
                label="Investments"
                model="investmentSearch"
                :options="collect($investmentOptions)->map(
                    fn ($option) => [
                        'value' => $option->value,
                        'label' => $option->value,
                    ],
                )"
                :selected="$investmentSearch"
                multiple compact
            />

            <x-dashboard-filter-dropdown
                label="Justifications"
                model="justificationSearch"
                :options="collect($justificationOptions)->map(
                    fn ($option) => [
                        'value' => $option->value,
                        'label' => $option->value,
                    ],
                )"
                :selected="$justificationSearch"
                multiple compact
            />



        </div>
        <p class="text-xs text-slate-500">{{ __('activity_control.filter_scope') }}</p>
        </div>
        @if (count($activeFilters))
            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3" aria-label="{{ __('activity_control.active_filters') }}">
                @foreach ($activeFilters as $chip)
                    <button type="button" wire:key="filter-{{ $chip['property'] }}-{{ md5($chip['value'] ?? '') }}"
                        wire:click="removeFilter({{ \Illuminate\Support\Js::from($chip['property']) }}, {{ \Illuminate\Support\Js::from($chip['value']) }})"
                        aria-label="{{ __('activity_control.remove_filter', ['filter' => $chip['label']]) }}"
                        class="inline-flex max-w-full items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-800 hover:bg-sky-100 focus:ring-2 focus:ring-sky-400">
                        <span class="break-words">{{ $chip['label'] }}</span><span aria-hidden="true">×</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</section>