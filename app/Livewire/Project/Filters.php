<?php

namespace App\Livewire\Project;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Filters extends Component
{
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
        ]);
    }
}
