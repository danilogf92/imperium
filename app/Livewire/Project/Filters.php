<?php

namespace App\Livewire\Project;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Filters extends Component
{
    public string $search = '';

    /**
     * Años disponibles obtenidos de los proyectos.
     *
     * @var array<int, string>
     */
    public array $years = [];

    /**
     * Plantas seleccionadas.
     *
     * @var array<int, string>
     */
    public array $plantFilter = [];

    /**
     * Año seleccionado.
     */
    public array $yearSearch = [];

    /**
     * Estado seleccionado.
     */
    public array $stateSearch = [];

    /**
     * Clasificación seleccionada.
     */
    public array $typeOfProjectSearch = [];

    /**
     * Tipo de inversión seleccionado.
     */
    public array $investmentFilter = [];
    public array $projectIdeaFilter = [];

    /**
     * Indica si se utiliza el ordenamiento especial.
     */
    public bool $orderByProject = false;

    /**
     * Texto del botón para ordenar.
     */
    public string $textButton = 'Order by Rest';

    /**
     * Inicializa el componente.
     */
    public function mount(): void
    {
        $this->loadYears();
    }

    /**
     * Carga los años existentes.
     */
    private function loadYears(): void
    {
        $this->years = $this->getYears();
    }

    /**
     * Activa o desactiva el ordenamiento por restante.
     */
    public function projectOrder(): void
    {
        $this->orderByProject = ! $this->orderByProject;

        $this->textButton = $this->orderByProject
            ? 'Clear Order'
            : 'Order by Rest';

        $this->dispatchFilters();
    }

    /**
     * Detecta cambios en los filtros.
     */
    public function updated(
        string $property,
        mixed $value
    ): void {
        $isPlantFilter = str_starts_with(
            $property,
            'plantFilter'
        );

        $isNormalFilter = collect([
                'yearSearch',
                'stateSearch',
                'typeOfProjectSearch',
                'investmentFilter',
                'projectIdeaFilter',
            ])->contains(
                fn(string $filter): bool => $property === $filter
                    || str_starts_with($property, $filter . '.')
            );

        if (! $isPlantFilter && ! $isNormalFilter) {
            return;
        }

        $this->dispatchFilters();
    }

    /**
     * Reinicia todos los filtros.
     */
    #[On('project-reset-all')]
    public function resetAll(): void
    {
        $this->resetNormalFilters();

        $this->orderByProject = false;
        $this->textButton = 'Order by Rest';

        $this->loadYears();

        $this->dispatchFilters();
    }

    /**
     * Actualiza los años cuando se crea un proyecto.
     */
    #[On('project-created')]
    public function refreshFilters(): void
    {
        $this->loadYears();
    }

    #[On('project-updated')]
    public function refreshUpdatedFilters(): void
    {
        $this->loadYears();
    }

    #[On('project-deleted')]
    public function refreshDeletedFilters(): void
    {
        $this->loadYears();
    }

    #[On('project-search-updated')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = trim($search);
    }

    /**
     * Limpia los filtros normales.
     */
    private function resetNormalFilters(): void
    {
        $this->plantFilter = [];
        $this->yearSearch = [];
        $this->stateSearch = [];
        $this->typeOfProjectSearch = [];
        $this->investmentFilter = [];
        $this->projectIdeaFilter = [];
        $this->search = '';
    }

    /**
     * Envía el estado de los filtros al componente Table.
     */
    private function dispatchFilters(): void
    {
        $this->dispatch(
            'project-filters-updated',
            plantFilter: $this->plantFilter,
            yearSearch: $this->yearSearch,
            stateSearch: $this->stateSearch,
            typeOfProjectSearch: $this->typeOfProjectSearch,
            investmentFilter: $this->investmentFilter,
            projectIdeaFilter: $this->projectIdeaFilter,
            orderByProject: $this->orderByProject,
        );
    }

    /**
     * Obtiene los años disponibles de los proyectos.
     *
     * @return array<int, string>
     */
    private function getYears(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::View
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->whereNotNull('forecast_start_date')
            ->orderByDesc('forecast_start_date')
            ->pluck('forecast_start_date')
            ->map(
                fn(mixed $date): string => Carbon::parse($date)
                    ->format('Y')
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Renderiza el componente.
     */
    public function render(): View
    {
        return view('livewire.project.filters', [
            /*
             * Los valores disponibles se obtienen de los enums.
             */
            'stateOptions' => ProjectStateEnum::cases(),

            'classificationOptions' =>
            InvestmentClassificationEnum::cases(),

            'investmentOptions' => InvestmentEnum::cases(),
            'companies' => auth()->user()?->companiesForPermission(
                ProjectPermissionEnum::View
            ) ?? collect(),
            'filteredProjectCount' => $this->filteredProjectCount(),
        ]);
    }

    private function filteredProjectCount(): int
    {
        $user = auth()->user();

        if (! $user) {
            return 0;
        }

        return Project::query()
            ->whereIn('company_id', $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                ->select('companies.id')->reorder())
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.$this->search.'%';
                $query->where(fn (Builder $query) => $query
                    ->where('name', 'like', $search)
                    ->orWhere('order', 'like', $search)
                    ->orWhere('pda_code', 'like', $search)
                    ->orWhere('state', 'like', $search)
                    ->orWhere('classification_of_investments', 'like', $search)
                    ->orWhere('investments', 'like', $search)
                    ->orWhere('justification', 'like', $search));
            })
            ->when($this->plantFilter !== [], fn (Builder $query) => $query->whereHas(
                'company',
                fn (Builder $query) => $query->whereIn('company_code', $this->plantFilter)
            ))
            ->when($this->yearSearch !== [], function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    foreach ($this->yearSearch as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            })
            ->when($this->stateSearch !== [], fn (Builder $query) => $query->whereIn('state', $this->stateSearch))
            ->when($this->typeOfProjectSearch !== [], fn (Builder $query) => $query->whereIn(
                'classification_of_investments',
                $this->typeOfProjectSearch
            ))
            ->when($this->investmentFilter !== [], fn (Builder $query) => $query->whereIn(
                'investments',
                $this->investmentFilter
            ))
            ->when(count($this->projectIdeaFilter) === 1, fn (Builder $query) =>
                $this->projectIdeaFilter[0] === 'with'
                    ? $query->whereNotNull('project_idea_path')
                    : $query->whereNull('project_idea_path'))
            ->when($this->orderByProject, fn (Builder $query) => $query
                ->where('state', '!=', 'Finished')
                ->where('data_uploaded', true)
                ->whereHas('data'))
            ->count();
    }
}
