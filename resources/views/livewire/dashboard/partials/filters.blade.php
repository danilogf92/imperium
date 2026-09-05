<section class="module-accent-line relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="font-semibold text-slate-900">
            {{ __($filterTitle ?? 'Dashboard filters') }}
        </h2>
    </div>

    <div class="p-4 sm:p-5">
        <div class="dashboard-filter-controls flex flex-wrap items-center gap-3">
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

            <x-dashboard-filter-dropdown
                label="Years"
                model="yearSearch"
                :options="collect($years)->map(fn ($year) => ['value' => $year, 'label' => $year])"
                :selected="$yearSearch"
                multiple
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

            @if ($showCurrency ?? true)
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
            @endif

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
