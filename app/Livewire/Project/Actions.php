<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Exports\ProjectDashboardExport;
use App\Exports\ProjectExport;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Actions extends Component
{
    use InteractsWithPerPagePreference;

    /**
     * Texto utilizado para buscar proyectos.
     */
    public string $search = '';

    /**
     * Cantidad de proyectos mostrados por página.
     */
    public int $perPage = 10;

    public array $plantFilter = [];
    public array $yearSearch = [];
    public array $stateSearch = [];
    public array $typeOfProjectSearch = [];
    public array $investmentFilter = [];
    public array $visibleColumns = [];
    public string $sortBy = 'order';
    public string $sortDir = 'ASC';
    public bool $orderByProject = false;

    public function mount(): void
    {
        $this->loadPerPagePreference();
    }

    #[On('project-filters-updated')]
    public function syncFilters(
        array $plantFilter = [], array $yearSearch = [], array $stateSearch = [],
        array $typeOfProjectSearch = [], array $investmentFilter = [],
        bool $orderByProject = false
    ): void {
        $this->plantFilter = $plantFilter;
        $this->yearSearch = $yearSearch;
        $this->stateSearch = $stateSearch;
        $this->typeOfProjectSearch = $typeOfProjectSearch;
        $this->investmentFilter = $investmentFilter;
        $this->orderByProject = $orderByProject;
    }

    #[On('project-table-state-updated')]
    public function syncTableState(array $visibleColumns, string $sortBy, string $sortDir): void
    {
        $this->visibleColumns = $visibleColumns;
        $this->sortBy = $sortBy;
        $this->sortDir = $sortDir;
    }

    /**
     * Envía el texto de búsqueda al componente Table.
     */
    public function updatedSearch(string $value): void
    {
        $this->dispatch(
            'project-search-updated',
            search: trim($value),
        );
    }

    /**
     * Envía la cantidad de registros al componente Table.
     */
    public function updatedPerPage(int|string $value): void
    {
        $this->savePerPagePreference($value);

        $this->dispatch(
            'project-per-page-updated',
            perPage: $this->perPage,
        );
    }

    /**
     * Limpia la búsqueda y solicita a Filters y Table reiniciar su estado.
     */
    public function resetAll(): void
    {
        $this->search = '';
        $this->plantFilter = [];
        $this->yearSearch = [];
        $this->stateSearch = [];
        $this->typeOfProjectSearch = [];
        $this->investmentFilter = [];
        $this->orderByProject = false;

        $this->dispatch('project-reset-all');
    }

    public function clearSearch(): void
    {
        $this->search = '';

        $this->dispatch(
            'project-search-updated',
            search: '',
        );
    }

    /**
     * Exporta la lista de proyectos.
     */
    public function export(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(
                ProjectPermissionEnum::Export
            )->exists(),
            403
        );

        return (new ProjectExport())->download($user, [
            'search' => $this->search,
            'plants' => $this->plantFilter,
            'years' => $this->yearSearch,
            'states' => $this->stateSearch,
            'classifications' => $this->typeOfProjectSearch,
            'investments' => $this->investmentFilter,
            'columns' => $this->visibleColumns,
            'sortBy' => $this->sortBy,
            'sortDir' => $this->sortDir,
            'orderByProject' => $this->orderByProject,
        ]);
    }

    public function exportDashboard(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(
                ProjectPermissionEnum::Export
            )->exists(),
            403
        );

        return (new ProjectDashboardExport())->download($user);
    }

    /**
     * Renderiza la barra de acciones.
     */
    public function render(): View
    {
        return view('livewire.project.actions', [
            'canExportProjects' => auth()->user()
                ?->companiesForPermissionQuery(
                    ProjectPermissionEnum::Export
                )
                ->exists() ?? false,
        ]);
    }
}
