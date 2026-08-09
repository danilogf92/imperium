<?php

namespace App\Livewire\Data\Concerns;

use App\Support\Data\DataTableDefinition;

trait InteractsWithDataFilters
{
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(int|string $value): void
    {
        $this->savePerPagePreference($value);
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedAreaFilter(): void
    {
        $this->resetPage();
    }

    public function updatedClassificationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedItemTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSupplierFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOrderYearFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            ...array_keys(DataTableDefinition::FILTER_COLUMNS),
        ]);

        $this->resetPage();
    }

    private function activeFilters(): array
    {
        return [
            'areaFilter' => $this->areaFilter,
            'classificationFilter' => $this->classificationFilter,
            'itemTypeFilter' => $this->itemTypeFilter,
            'stageFilter' => $this->stageFilter,
            'supplierFilter' => $this->supplierFilter,
            'orderYearFilter' => $this->orderYearFilter,
        ];
    }

    private function hasActiveFilters(): bool
    {
        if (trim($this->search) !== '') {
            return true;
        }

        return collect(DataTableDefinition::FILTER_COLUMNS)
            ->keys()
            ->contains(
                fn (string $property) =>
                    $this->{$property} !== []
            );
    }
}
