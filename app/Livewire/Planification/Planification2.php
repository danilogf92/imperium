<?php

namespace App\Livewire\Planification;

use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Exports\PlanificationExport;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Planification extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Propiedades del formulario de planificación
    |--------------------------------------------------------------------------
    |
    | Estas propiedades representan los campos del modal para crear o editar
    | un milestone dentro de la planificación de un proyecto.
    |
    */

    public ?int $projectId = null;

    public ?int $milestoneId = null;

    public ?int $month = null;

    public ?int $cycleYear = null;

    public ?int $editingId = null;

    public string $percentage = '0';

    /*
    |--------------------------------------------------------------------------
    | Configuración de visualización
    |--------------------------------------------------------------------------
    |
    | currency:
    |   - usd
    |   - eur
    |
    | cellDisplay:
    |   - combined  => milestone + valor
    |   - milestone => solo milestone
    |   - value     => solo valor
    |
    */

    public string $currency = 'usd';

    public string $cellDisplay = 'combined';

    /*
    |--------------------------------------------------------------------------
    | Estado de modales
    |--------------------------------------------------------------------------
    */

    public bool $showFormModal = false;

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteLabel = '';

    /*
    |--------------------------------------------------------------------------
    | Buscador, paginación y filtros
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public int $perPage = 10;

    public array $plantFilter = [];

    public array $statusFilter = [];

    public array $creationYearFilter = [];

    public bool $onlyWithMilestones = false;

    /*
    |--------------------------------------------------------------------------
    | Eventos de propiedades Livewire
    |--------------------------------------------------------------------------
    */

    /**
     * Cuando cambia el proyecto seleccionado dentro del modal,
     * ajustamos automáticamente el año de planificación.
     *
     * IMPORTANTE:
     * No llamamos resetPage() aquí porque eso provocaba un re-renderizado
     * innecesario de toda la tabla mientras el modal estaba abierto.
     */
    public function updatedProjectId(): void
    {
        if ($this->showFormModal && ! $this->editingId && $this->projectId) {
            $project = $this->authorizedProjects()->find($this->projectId);

            $this->cycleYear = $project?->forecast_start_date?->year ?? now()->year;
        }
    }

    /**
     * Al cambiar el buscador volvemos a la primera página.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Limpia el texto de búsqueda.
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Cambiar el número de registros por página debe regresar a página 1.
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Cada filtro vuelve a la página 1 para evitar quedar en una página
     * que ya no exista después de aplicar el filtro.
     */
    public function updatedPlantFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCreationYearFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Seguridad básica para evitar valores de currency no permitidos.
     */
    public function updatedCurrency(string $value): void
    {
        if (! in_array($value, ['usd', 'eur'], true)) {
            $this->currency = 'usd';
        }
    }

    /**
     * Seguridad básica para evitar modos de visualización no permitidos.
     */
    public function updatedCellDisplay(string $value): void
    {
        if (! in_array($value, ['combined', 'milestone', 'value'], true)) {
            $this->cellDisplay = 'combined';
        }
    }

    /**
     * Activa/desactiva el filtro que muestra únicamente proyectos
     * que ya tienen milestones.
     */
    public function toggleOnlyWithMilestones(): void
    {
        $this->onlyWithMilestones = ! $this->onlyWithMilestones;
        $this->resetPage();
    }

    /**
     * Restablece todos los filtros de la pantalla.
     */
    public function clearFilters(): void
    {
        $this->reset([
            'plantFilter',
            'statusFilter',
            'creationYearFilter',
            'onlyWithMilestones',
        ]);

        $this->currency = 'usd';
        $this->cellDisplay = 'combined';

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Exportación
    |--------------------------------------------------------------------------
    */

    /**
     * Exporta la planificación respetando los filtros actuales.
     */
    public function exportExcel(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(ProjectPermissionEnum::Export)->exists(),
            403
        );

        return (new PlanificationExport)->download($user, [
            'search' => $this->search,
            'plants' => $this->plantFilter,
            'statuses' => $this->statusFilter,
            'creationYears' => $this->creationYearFilter,
            'onlyWithMilestones' => $this->onlyWithMilestones,
            'currency' => $this->currency,
            'cellDisplay' => $this->cellDisplay,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Apertura y cierre del modal
    |--------------------------------------------------------------------------
    */

    /**
     * Abre un modal vacío para crear un milestone manualmente.
     */
    public function openCreate(): void
    {
        $this->resetValidation();

        $this->reset([
            'projectId',
            'milestoneId',
            'month',
            'cycleYear',
            'editingId',
        ]);

        $this->percentage = '0';

        $this->showFormModal = true;
    }

    /**
     * Abre el modal desde una celda específica de la tabla.
     *
     * Recibe:
     * - proyecto
     * - año
     * - mes
     */
    public function openCreateAt(int $projectId, int $year, int $month): void
    {
        $project = $this->authorizedProjects()->findOrFail($projectId);

        $this->ensureProjectIsOpen($project);

        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->min('cycle_year')
            ?? now()->year;

        /*
         * Cada proyecto solamente puede planificarse dentro de dos años
         * consecutivos: año inicial y año inicial + 1.
         */
        abort_unless(
            in_array($year, [$firstYear, $firstYear + 1], true),
            422
        );

        $this->resetValidation();

        $this->editingId = null;
        $this->projectId = $project->id;
        $this->milestoneId = null;
        $this->month = $month;
        $this->cycleYear = $year;
        $this->percentage = '0';

        $this->showFormModal = true;
    }

    /**
     * Carga un milestone existente en el formulario.
     */
    public function editMilestone(int $projectMilestoneId): void
    {
        $item = $this->authorizedProjectMilestone($projectMilestoneId);

        $this->resetValidation();

        $this->editingId = $item->id;
        $this->projectId = $item->project_id;
        $this->milestoneId = $item->milestone_id;
        $this->month = $item->month;
        $this->cycleYear = $item->cycle_year;
        $this->percentage = (string) $item->percentage;

        $this->showFormModal = true;
    }

    /**
     * Cierra únicamente el modal de formulario.
     */
    public function closeForm(): void
    {
        $this->showFormModal = false;
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Crear / editar milestone
    |--------------------------------------------------------------------------
    */

    /**
     * Guarda un milestone nuevo o actualiza uno existente.
     */
    public function saveMilestone(): void
    {
        /*
         * Validación básica de los datos enviados desde el formulario.
         */
        $validated = $this->validate([
            'projectId' => ['required', 'integer'],
            'milestoneId' => ['required', 'integer', 'exists:milestones,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'cycleYear' => ['required', 'integer', 'between:2000,2200'],
            'percentage' => ['required', 'numeric', 'between:0,100'],
        ]);

        /*
         * El proyecto debe pertenecer a una empresa que el usuario
         * tenga permiso de visualizar.
         */
        $project = $this->authorizedProjects()
            ->find($validated['projectId']);

        if (! $project) {
            throw ValidationException::withMessages([
                'projectId' => 'You do not have permission to plan this project.',
            ]);
        }

        /*
         * Solo comprobamos proyecto cerrado cuando estamos creando.
         * Un milestone existente puede seguir siendo editado.
         */
        if (! $this->editingId) {
            $this->ensureProjectIsOpen($project);
        }

        $selectedMilestone = Milestone::query()
            ->findOrFail($validated['milestoneId']);

        /*
         * Determinamos el primer año disponible para este proyecto.
         */
        $firstYear = $project->forecast_start_date?->year
            ?? ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->min('cycle_year')
            ?? (int) $validated['cycleYear'];

        /*
         * Solo permitimos dos años consecutivos.
         */
        if (! in_array(
            (int) $validated['cycleYear'],
            [$firstYear, $firstYear + 1],
            true
        )) {
            throw ValidationException::withMessages([
                'cycleYear' => "A project can only use {$firstYear} and ".($firstYear + 1).'.',
            ]);
        }

        /*
         * Obtenemos el primer milestone cronológico del proyecto.
         */
        $firstMilestone = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->orderBy('cycle_year')
            ->orderBy('month')
            ->orderBy('sequence')
            ->first();

        /*
         * Convertimos año + mes a una posición comparable.
         *
         * Ejemplo:
         * 2026 * 12 + 8
         */
        $requestedPosition = ((int) $validated['cycleYear'] * 12)
            + (int) $validated['month'];

        $firstPosition = $firstMilestone
            ? ($firstMilestone->cycle_year * 12) + $firstMilestone->month
            : null;

        /*
         * No se permite mover/agregar un milestone antes del comienzo
         * actual de la planificación.
         */
        if (
            $firstPosition !== null
            && $requestedPosition < $firstPosition
            && (! $this->editingId || $this->editingId !== $firstMilestone->id)
        ) {
            throw ValidationException::withMessages(['month' => 'A milestone cannot be placed before the project plan start month.']);
        }

        /*
         * Buscamos si existe el milestone CLOSED en este proyecto.
         */
        $closedItem = ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when(
                $this->editingId,
                fn (Builder $query) => $query->whereKeyNot($this->editingId)
            )
            ->whereHas(
                'milestone',
                fn (Builder $query) => $query
                    ->whereRaw('UPPER(code) = ?', ['CLOSED'])
            )
            ->first();

        /*
         * Si ya existe CLOSED, no se permiten milestones posteriores.
         */
        if ($closedItem && strtoupper($selectedMilestone->code) !== 'CLOSED') {
            $closedPosition = ($closedItem->cycle_year * 12)
                + $closedItem->month;

            if (! $this->editingId || $requestedPosition > $closedPosition) {
                throw ValidationException::withMessages(['milestoneId' => 'Milestones cannot be added or moved after Closed Project.']);
            }
        }

        /*
         * CLOSED siempre debe ser el último milestone cronológicamente.
         */
        if (strtoupper($selectedMilestone->code) === 'CLOSED') {
            $hasLaterItems = ProjectMilestone::query()
                ->where('project_id', $project->id)
                ->when($this->editingId, fn (Builder $query) => $query->whereKeyNot($this->editingId))
                ->whereRaw('(cycle_year * 12) + month > ?', [$requestedPosition])
                ->exists();

            if ($hasLaterItems) {
                throw ValidationException::withMessages(['month' => 'Closed Project must be the final milestone in the timeline.']);
            }
        }

        /*
         * La suma de porcentajes de milestones no puede superar 100%.
         */
        $allocatedPercentage = (float) ProjectMilestone::query()
            ->where('project_id', $project->id)
            ->when($this->editingId, fn (Builder $query) => $query->whereKeyNot($this->editingId))
            ->sum('percentage');

        if ($allocatedPercentage + (float) $validated['percentage'] > 100.00001) {
            throw ValidationException::withMessages(['percentage' => 'The project milestone percentages cannot exceed 100%.']);
        }

        /*
         * Guardamos dentro de una transacción para mantener consistencia
         * entre el milestone y el reordenamiento de secuencias.
         */
        DB::transaction(function () use ($project, $validated): void {
            $item = $this->editingId ? $this->authorizedProjectMilestone($this->editingId) : new ProjectMilestone(['project_id' => $project->id]);

            $item->fill([
                'project_id' => $project->id,
                'milestone_id' => $validated['milestoneId'],
                'month' => (int) $validated['month'],
                'cycle_year' => (int) $validated['cycleYear'],
                'percentage' => (float) $validated['percentage'],
            ]);

            /*
             * Para un registro nuevo damos una secuencia provisional.
             * Después resequenceProject() organiza todo correctamente.
             */
            if (! $item->exists) {
                $item->sequence = (int) ProjectMilestone::query()->where('project_id', $project->id)->max('sequence') + 1;
            }

            $item->save();
            $this->resequenceProject($project->id);
        });

        /*
         * Mensaje según si creamos o editamos.
         */
        $message = $this->editingId
            ? 'Milestone updated successfully.'
            : 'Milestone added successfully.';

        /*
         * Cerramos el modal y limpiamos campos.
         */
        $this->showFormModal = false;

        $this->reset([
            'milestoneId',
            'month',
            'cycleYear',
            'editingId',
        ]);

        $this->percentage = '0';

        $this->resetPage();

        session()->flash(
            'planification-status',
            $message
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminación de milestones
    |--------------------------------------------------------------------------
    */

    /**
     * Prepara el modal de confirmación.
     */
    public function requestDeleteMilestone(int $projectMilestoneId): void
    {
        $item = $this->authorizedProjectMilestone($projectMilestoneId);

        $this->pendingDeleteId = $item->id;

        $this->pendingDeleteLabel = "{$item->milestone->code} — {$item->project->name}";
    }

    /**
     * Cancela la eliminación.
     */
    public function cancelDelete(): void
    {
        $this->reset([
            'pendingDeleteId',
            'pendingDeleteLabel',
        ]);
    }

    /**
     * Elimina definitivamente el milestone.
     */
    public function confirmDeleteMilestone(): void
    {
        if (! $this->pendingDeleteId) {
            return;
        }

        $item = $this->authorizedProjectMilestone(
            $this->pendingDeleteId
        );

        $projectId = $item->project_id;

        $item->delete();

        $this->resequenceProject($projectId);

        $this->reset(['pendingDeleteId', 'pendingDeleteLabel']);

        session()->flash('planification-status', 'Milestone removed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Render principal
    |--------------------------------------------------------------------------
    */

    /**
     * Construye todos los datos necesarios para la vista.
     */
    public function render(): View
    {
        /*
         * Consulta principal de proyectos que aparecen en la tabla.
         */
        $plannedProjects = $this->authorizedProjects()
            ->with([
                'company:id,company_name',

                'projectMilestones' => fn ($query) => $query
                    ->with('milestone:id,name,code,color')
                    ->orderBy('cycle_year')
                    ->orderBy('sequence'),
            ])

            /*
             * Calculamos los presupuestos desde la relación data.
             */
            ->withSum(
                'data as data_budgeted',
                'global_price'
            )
            ->withSum(
                'data as data_budgeted_euros',
                'global_price_euros'
            )

            /*
             * Filtro de planta.
             */
            ->when(
                $this->plantFilter !== [],
                fn (Builder $query) => $query
                    ->whereIn('company_id', $this->plantFilter)
            )

            /*
             * Filtro de estado.
             */
            ->when(
                $this->statusFilter !== [],
                fn (Builder $query) => $query
                    ->whereIn('state', $this->statusFilter)
            )

            /*
             * Filtro por año de forecast_start_date.
             */
            ->when(
                $this->creationYearFilter !== [],
                function (Builder $query): void {
                    $query->where(
                        function (Builder $query): void {
                            foreach ($this->creationYearFilter as $year) {
                                $query->orWhereYear(
                                    'forecast_start_date',
                                    $year
                                );
                            }
                        }
                    );
                }
            )

            /*
             * Solo proyectos que tengan milestones.
             */
            ->when($this->onlyWithMilestones, fn (Builder $query) => $query->whereHas('projectMilestones'))

            /*
             * Buscador general.
             */
            ->when(
                $this->search !== '',
                function (Builder $query): void {
                    $search = '%'.trim($this->search).'%';

                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', $search)
                                ->orWhere('pda_code', 'like', $search)
                                ->orWhere('state', 'like', $search)
                                ->orWhereHas('company', fn (Builder $query) => $query->where('company_name', 'like', $search))
                                ->orWhereHas(
                                    'projectMilestones.milestone',
                                    fn (Builder $query) => $query->where('name', 'like', $search)->orWhere('code', 'like', $search)
                                );
                        }
                    );
                }
            )

            ->orderBy('name')
            ->paginate($this->perPage);

        /*
         * Determinamos qué años deben aparecer como columnas
         * dentro de la línea de tiempo.
         */
        $timelineYears = $plannedProjects
            ->getCollection()
            ->flatMap(function (Project $project) {
                $firstYear = $project->forecast_start_date?->year ?? $project->projectMilestones->min('cycle_year') ?? now()->year;

                return $project->projectMilestones->pluck('cycle_year')->push($firstYear, $firstYear + 1);
            })
            ->unique()
            ->sort()
            ->values();

        /*
         * Consulta ligera para construir las opciones de filtros.
         */
        $filterProjects = $this->authorizedProjects()
            ->with('company:id,company_name')
            ->get(['id', 'company_id', 'state', 'forecast_start_date']);

        /*
         * Enviamos todos los datos a la vista Blade.
         */
        return view(
            'livewire.planification.planification',
            [
                'plannedProjects' => $plannedProjects,
                'timelineYears' => $timelineYears,

                /*
                 * Proyectos disponibles dentro del modal.
                 */
                'projects' => $this->authorizedProjects()
                    ->withExists(['projectMilestones as is_closed' => fn (Builder $query) => $query->whereHas('milestone', fn (Builder $query) => $query->whereRaw('UPPER(code) = ?', ['CLOSED']))])
                    ->withSum('data as data_budgeted', 'global_price')
                    ->withSum('data as data_budgeted_euros', 'global_price_euros')
                    ->orderBy('name')
                    ->get(['id', 'name', 'forecast_start_date']),

                /*
                 * Catálogo de milestones.
                 */
                'milestones' => Milestone::query()->orderBy('name')->get(['id', 'name', 'code', 'color']),

                /*
                 * Opciones únicas de plantas.
                 */
                'plantOptions' => $filterProjects
                    ->filter(fn (Project $project) => $project->company)
                    ->map(fn (Project $project) => ['id' => $project->company_id, 'name' => $project->company->company_name])
                    ->unique('id')
                    ->sortBy('name')
                    ->values(),

                /*
                 * Estados disponibles.
                 */
                'statusOptions' => ProjectStateEnum::values(),

                /*
                 * Años disponibles.
                 */
                'creationYearOptions' => $filterProjects
                    ->map(fn (Project $project) => $project->forecast_start_date?->year)
                    ->filter()
                    ->unique()
                    ->sortDesc()
                    ->values(),

                /*
                 * Permiso para exportar.
                 */
                'canExport' => auth()->user()?->companiesForPermissionQuery(ProjectPermissionEnum::Export)->exists() ?? false,

                /*
                 * Catálogo de meses.
                 */
                'months' => [
                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',
                ],
            ]
        )->layout('layouts.app');
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad y consultas internas
    |--------------------------------------------------------------------------
    */

    /**
     * Devuelve únicamente proyectos pertenecientes a empresas
     * que el usuario tiene permitido visualizar.
     */
    private function authorizedProjects(): Builder
    {
        return Project::query()->whereIn('company_id', $this->allowedCompanyIds());
    }

    /**
     * IDs de empresas permitidas para el usuario.
     */
    private function allowedCompanyIds(): Builder
    {
        return auth()->user()->companiesForPermissionQuery(ProjectPermissionEnum::View)->select('companies.id');
    }

    /**
     * Busca un ProjectMilestone y verifica que el usuario tenga
     * permiso sobre el proyecto relacionado.
     */
    private function authorizedProjectMilestone(int $id): ProjectMilestone
    {
        return ProjectMilestone::query()
            ->whereKey($id)
            ->whereHas('project', fn (Builder $query) => $query->whereIn('company_id', $this->allowedCompanyIds()))
            ->firstOrFail();
    }

    /**
     * Reordena la propiedad sequence del proyecto según año y mes.
     */
    private function resequenceProject(int $projectId): void
    {
        /*
         * Primero desplazamos las secuencias existentes para evitar
         * posibles conflictos si existe índice único.
         */
        ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->increment('sequence', 100000);

        /*
         * Después asignamos secuencia 1, 2, 3...
         */
        ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->orderBy('cycle_year')
            ->orderBy('month')
            ->orderBy('id')
            ->get()
            ->each(fn (ProjectMilestone $item, int $index) => $item->update(['sequence' => $index + 1]));
    }

    /**
     * Evita agregar milestones nuevos a un proyecto cerrado.
     */
    private function ensureProjectIsOpen(Project $project): void
    {
        $isClosed = $project
            ->projectMilestones()
            ->whereHas('milestone', fn (Builder $query) => $query->whereRaw('UPPER(code) = ?', ['CLOSED']))
            ->exists();

        if ($isClosed) {
            throw ValidationException::withMessages(['projectId' => 'This project is closed and cannot receive more milestones.']);
        }
    }
}
