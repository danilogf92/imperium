<section class="relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="font-semibold text-slate-900">
            {{ __('Dashboard filters') }}
        </h2>
    </div>

    <div class="overflow-x-auto p-5">
        <div class="flex min-w-max items-center gap-3">
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
                multiple
            />

            <div
                x-data="{ open: false }"
                x-on:scroll.window="open = false"
                class="shrink-0"
            >
                <button
                    x-ref="trigger"
                    type="button"
                    @click="
                        open = !open;

                        if (open) {
                            $nextTick(() => {
                                const rect = $refs.trigger.getBoundingClientRect();

                                $refs.menu.style.left = `${rect.left}px`;
                                $refs.menu.style.top = `${rect.bottom + 8}px`;
                            });
                        }
                    "
                    :class="open
                        ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700'
                        : 'border-slate-300'"
                    class="inline-flex h-11 w-32 cursor-pointer items-center justify-between rounded-lg border bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                >
                    <span>{{ __('Years') }}</span>

                    <span class="flex items-center gap-2">
                        @if (count($yearSearch) > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-xs font-semibold text-white">
                                {{ count($yearSearch) }}
                            </span>
                        @endif

                        <svg
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': open }"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m19.5 8.25-7.5 7.5-7.5-7.5"
                            />
                        </svg>
                    </span>
                </button>

                <template x-teleport="body">
                    <div
                        x-ref="menu"
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        class="fixed z-[200] w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                    >
                        <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Select years') }}
                        </p>

                        <div class="space-y-1">
                            @foreach ($years as $year)
                                <label @class([
                                    'flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm transition duration-150 hover:bg-blue-100',
                                    'bg-blue-50 font-medium text-blue-700' => in_array(
                                        $year,
                                        $yearSearch,
                                        true
                                    ),
                                    'text-slate-700' => ! in_array($year, $yearSearch, true),
                                ])>
                                    <input
                                        wire:model.live="yearSearch"
                                        data-global-loading
                                        type="checkbox"
                                        value="{{ $year }}"
                                        class="h-4 w-4 rounded border-slate-300 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                    >

                                    <span>{{ $year }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </template>
            </div>

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
                multiple
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
                multiple
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
                multiple
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
                multiple
            />

            <x-dashboard-filter-dropdown
                label="Currency"
                model="currency"
                :options="[
                    ['value' => 'euro', 'label' => 'Euro'],
                    ['value' => 'dollar', 'label' => 'Dollar ($)'],
                ]"
                :selected="$currency"
                default="euro"
            />

            <x-clear-filters-button
                method="resetAll"
                :active="
                    $companyFilter !== [] ||
                    $yearSearch !== [] ||
                    $stateSearch !== [] ||
                    $typeOfProjectSearch !== [] ||
                    $investmentSearch !== [] ||
                    $justificationSearch !== [] ||
                    $currency !== 'euro'
                "
            />
        </div>
    </div>
</section>
