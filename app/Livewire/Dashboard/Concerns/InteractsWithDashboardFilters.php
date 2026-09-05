<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Support\Dashboard\DashboardFilters;

trait InteractsWithDashboardFilters
{
    public array $yearSearch = [];

    public array $stateSearch = [];

    public array $typeOfProjectSearch = [];

    public array $investmentSearch = [];

    public array $justificationSearch = [];

    public array $companyFilter = [];

    public array $years = [];

    public string $currency = 'euro';

    public function resetAll(): void
    {
        $this->reset([
            'yearSearch',
            'stateSearch',
            'typeOfProjectSearch',
            'investmentSearch',
            'justificationSearch',
            'companyFilter',
        ]);

        $this->currency = 'euro';
    }

    private function filters(): DashboardFilters
    {
        return new DashboardFilters(
            companies: $this->companyFilter,
            years: $this->yearSearch,
            states: $this->stateSearch,
            classifications: $this->typeOfProjectSearch,
            investments: $this->investmentSearch,
            justifications: $this->justificationSearch,
            currency: $this->currency,
        );
    }

    private function sanitizeFilters(): void
    {
        $this->companyFilter = $this->normalizeSelection(
            $this->companyFilter,
            auth()->user()?->availableCompanyCodes() ?? []
        );

        $this->yearSearch = $this->normalizeSelection($this->yearSearch, $this->years);

        $this->stateSearch = $this->normalizeSelection(
            $this->stateSearch,
            array_column($this->reportableStateOptions(), 'value')
        );

        $this->typeOfProjectSearch = $this->normalizeSelection(
            $this->typeOfProjectSearch,
            array_column(InvestmentClassificationEnum::cases(), 'value')
        );

        $this->investmentSearch = $this->normalizeSelection(
            $this->investmentSearch,
            array_column(InvestmentEnum::cases(), 'value')
        );

        $this->justificationSearch = $this->normalizeSelection(
            $this->justificationSearch,
            array_column(ProjectJustificationEnum::cases(), 'value')
        );

        if (! in_array($this->currency, ['euro', 'dollar'], true)) {
            $this->currency = 'euro';
        }
    }

    private function normalizeSelection(array $values, array $allowed): array
    {
        $normalized = array_values(array_unique(array_intersect($values, $allowed)));
        sort($normalized);

        return $normalized;
    }

    private function reportableStateOptions(): array
    {
        return array_values(array_filter(
            ProjectStateEnum::cases(),
            fn (ProjectStateEnum $state): bool => $state !== ProjectStateEnum::Postponed
        ));
    }
}
