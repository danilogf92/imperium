<?php

namespace App\Livewire\Project\Concerns;

use App\Support\Project\ProjectTableDefinition;
use Livewire\Attributes\On;

trait InteractsWithProjectFilters
{
    #[On('project-search-updated')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = trim($search);
        $this->resetPage();
    }

    #[On('project-per-page-updated')]
    public function updatePerPage(int|string $perPage = 10): void
    {
        $this->savePerPagePreference($perPage);
        $this->resetPage();
    }

    #[On('project-filters-updated')]
    public function updateFilters(
        array $plantFilter = [], array $yearSearch = [], array $stateSearch = [],
        array $typeOfProjectSearch = [], array $investmentFilter = [],
        array $projectIdeaFilter = [],
        bool $orderByProject = false
    ): void {
        $allowedPlants = auth()->user()?->availableCompanyCodes() ?? [];
        $this->plantFilter = array_values(array_intersect($plantFilter, $allowedPlants));
        $this->yearSearch = $yearSearch;
        $this->stateSearch = $stateSearch;
        $this->typeOfProjectSearch = $typeOfProjectSearch;
        $this->investmentFilter = $investmentFilter;
        $this->projectIdeaFilter = array_values(array_intersect($projectIdeaFilter, ['with', 'without']));
        $this->orderByProject = $orderByProject;
        $this->resetPage();
    }

    #[On('project-reset-all')]
    public function resetAll(): void
    {
        $this->reset([
            'search', 'yearSearch', 'stateSearch', 'typeOfProjectSearch',
            'investmentFilter', 'plantFilter', 'orderByProject',
            'projectIdeaFilter',
        ]);
        $this->sortBy = 'order';
        $this->sortDir = 'ASC';
        $this->resetPage();
        $this->dispatchTableState();
    }

    public function setSortBy(string $column): void
    {
        if (! in_array($column, ProjectTableDefinition::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'DESC';
        }

        $this->orderByProject = false;
        $this->resetPage();
        $this->dispatchTableState();
    }

    private function dispatchTableState(): void
    {
        $this->dispatch('project-table-state-updated',
            visibleColumns: $this->visibleColumns,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
        );
    }
}
