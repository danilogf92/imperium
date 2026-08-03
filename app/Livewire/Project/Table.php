<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Models\UserPreference;
use App\Services\ProjectDataExcelImporter;
use App\Validation\ProjectDocumentUploadValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Table extends Component
{
    use WithFileUploads;
    use WithPagination;

    private const COLUMNS_PREFERENCE_KEY = 'projects.table.visible_columns.v3';

    private const COLUMN_OPTIONS = [
        'id' => 'ID',
        'order' => 'Order',
        'plant' => 'Plant',
        'pda_code' => 'PDA code',
        'forecast_start_date' => 'Forecast Start Year',
        'investments' => 'Investments',
        'state' => 'State',
        'budgeted_euros' => 'Budgeted Euros',
        'forecast_end_date' => 'Forecast End Date',
        'real_euros' => 'Real Euros',
        'rate' => 'Rate',
        'budgeted_dollars' => 'Budgeted Dollars',
        'real_dollars' => 'Real Dollars',
        'upload_pda' => 'Upload PDA',
        'name' => 'Name',
        'links' => 'Links',
        'classification' => 'Classification',
        'justification' => 'Justification',
        'creator' => 'Created By',
        'responsible' => 'Responsible',
        'data_uploaded' => 'Data Uploaded',
        'quartile_date' => 'Quartile Date',
        'approve_date' => 'Approved Date',
        'close_date' => 'Close Date',
        'file_name' => 'Document Name',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'actions' => 'Actions',
    ];

    private const DEFAULT_COLUMNS = [
        'id',
        'order',
        'plant',
        'pda_code',
        'forecast_start_date',
        'investments',
        'state',
        'budgeted_euros',
        'forecast_end_date',
        'real_euros',
        'rate',
        'actions',
    ];

    public bool $active = false;

    public int $perPage = 10;

    public string $search = '';

    public string $sortBy = 'id';

    public string $sortDir = 'DESC';

    public array $yearSearch = [];

    public array $stateSearch = [];

    public array $typeOfProjectSearch = [];

    public array $investmentFilter = [];

    /**
     * Plantas seleccionadas.
     *
     * @var array<int, string>
     */
    public array $plantFilter = [];

    public bool $orderByProject = false;

    public array $visibleColumns = [];

    public mixed $document = null;

    public ?int $documentProjectId = null;

    public string $documentProjectName = '';

    public string $documentProjectCode = '';

    public ?string $currentDocumentName = null;

    public ?int $deleteDocumentProjectId = null;

    public string $deleteDocumentProjectName = '';

    public string $deleteDocumentProjectCode = '';

    public string $deleteDocumentName = '';

    public mixed $dataImportFile = null;

    public ?int $dataImportProjectId = null;

    public string $dataImportProjectName = '';

    public string $dataImportProjectCode = '';

    public int $dataImportExistingRows = 0;

    /**
     * Columnas permitidas para ordenar.
     *
     * @var array<int, string>
     */
    private array $sortableColumns = [
        'id',
        'order',
        'name',
        'pda_code',
        'rate',
        'state',
        'investments',
        'classification_of_investments',
        'justification',
        'forecast_start_date',
        'forecast_end_date',
        'data_uploaded',
        'quartile_date',
        'approve_date',
        'close_date',
        'file_name',
        'created_at',
        'updated_at',
        'budgeted_euros',
        'real_euros',
        'budgeted_dollars',
        'real_dollars',
    ];

    #[On('project-created')]
    public function refreshProjects(): void
    {
        $this->resetPage();
    }

    #[On('project-updated')]
    public function refreshUpdatedProject(): void
    {
        $this->resetPage();
    }

    #[On('project-deleted')]
    public function refreshDeletedProject(): void
    {
        $this->resetPage();
    }

    public function mount(bool $active = false): void
    {
        $this->active = $active;
        $this->visibleColumns = $this->sanitizeVisibleColumns(
            auth()->user()?->preferences()
                ->where('key', self::COLUMNS_PREFERENCE_KEY)
                ->first()?->value ?? self::DEFAULT_COLUMNS
        );
        $this->dispatchTableState();
    }

    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeVisibleColumns(
            $this->visibleColumns
        );

        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = self::DEFAULT_COLUMNS;
        $this->saveVisibleColumnsPreference();
        $this->dispatchTableState();
    }

    private function saveVisibleColumnsPreference(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        UserPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'key' => self::COLUMNS_PREFERENCE_KEY,
            ],
            ['value' => $this->visibleColumns]
        );
    }

    private function sanitizeVisibleColumns(mixed $columns): array
    {
        $columns = array_values(array_intersect(
            array_keys(self::COLUMN_OPTIONS),
            (array) $columns
        ));

        $columns = array_values(array_diff($columns, ['actions']));
        $columns[] = 'actions';

        return count($columns) > 1 ? $columns : self::DEFAULT_COLUMNS;
    }

    public function openDocumentModal(int $projectId): void
    {
        $project = $this->authorizedDocumentProject($projectId);

        $this->documentProjectId = (int) $project->id;
        $this->documentProjectName = $project->name;
        $this->documentProjectCode = $project->pda_code;
        $this->currentDocumentName = $project->file_name;
        $this->reset('document');
        $this->resetValidation('document');
        $this->dispatch('open-modal', 'upload-project-document');
    }

    public function closeDocumentModal(): void
    {
        $this->reset([
            'document',
            'documentProjectId',
            'documentProjectName',
            'documentProjectCode',
            'currentDocumentName',
        ]);
        $this->resetValidation('document');
        $this->dispatch('close-modal', 'upload-project-document');
    }

    public function uploadDocument(): void
    {
        abort_unless($this->documentProjectId, 404);
        $project = $this->authorizedDocumentProject($this->documentProjectId);
        abort_if(filled($project->upload_pda), 409, 'Delete the current document before uploading another one.');

        $this->validate(
            ProjectDocumentUploadValidation::rules(),
            ProjectDocumentUploadValidation::messages(),
            ProjectDocumentUploadValidation::attributes()
        );

        $originalName = $this->document->getClientOriginalName();
        $extension = strtolower($this->document->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'document';
        $fileName = now()->format('YmdHis').'-'.$baseName.'.'.$extension;
        $path = $this->document->storeAs(
            "projects/{$project->id}/documents",
            $fileName,
            'public'
        );
        $previousPath = $project->upload_pda;

        $project->update([
            'upload_pda' => $path,
            'file_name' => $originalName,
        ]);

        if (
            filled($previousPath)
            && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/documents/")
        ) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Document uploaded',
            position: 'center',
            timer: 1800
        );
        $this->closeDocumentModal();
    }

    public function openDeleteDocumentModal(int $projectId): void
    {
        $project = $this->authorizedDocumentProject($projectId);

        abort_if(blank($project->upload_pda), 404);

        $this->deleteDocumentProjectId = (int) $project->id;
        $this->deleteDocumentProjectName = $project->name;
        $this->deleteDocumentProjectCode = $project->pda_code;
        $this->deleteDocumentName = $project->file_name ?: basename($project->upload_pda);
        $this->dispatch('open-modal', 'delete-project-document');
    }

    public function closeDeleteDocumentModal(): void
    {
        $this->reset([
            'deleteDocumentProjectId',
            'deleteDocumentProjectName',
            'deleteDocumentProjectCode',
            'deleteDocumentName',
        ]);
        $this->dispatch('close-modal', 'delete-project-document');
    }

    public function deleteDocument(): void
    {
        abort_unless($this->deleteDocumentProjectId, 404);
        $project = $this->authorizedDocumentProject($this->deleteDocumentProjectId);
        $path = $project->upload_pda;

        if (blank($path)) {
            $this->closeDeleteDocumentModal();

            return;
        }

        $project->update([
            'upload_pda' => null,
            'file_name' => null,
        ]);

        if (str_starts_with($path, "projects/{$project->id}/documents/")) {
            Storage::disk('public')->delete($path);
        }

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Document deleted',
            position: 'center',
            timer: 1800
        );
        $this->closeDeleteDocumentModal();
    }

    public function downloadDocument(): BinaryFileResponse
    {
        abort_unless($this->deleteDocumentProjectId, 404);
        $project = $this->authorizedDocumentProject($this->deleteDocumentProjectId);
        abort_if(blank($project->upload_pda), 404);
        abort_unless(Storage::disk('public')->exists($project->upload_pda), 404);

        return response()->download(
            Storage::disk('public')->path($project->upload_pda),
            $project->file_name ?: basename($project->upload_pda)
        );
    }

    private function authorizedDocumentProject(int $projectId): Project
    {
        $user = auth()->user();
        abort_unless($user, 403);

        return Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::Update)
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($projectId);
    }

    public function openDataImportModal(int $projectId): void
    {
        $project = $this->authorizedDocumentProject($projectId);

        $this->dataImportProjectId = (int) $project->id;
        $this->dataImportProjectName = $project->name;
        $this->dataImportProjectCode = $project->pda_code;
        $this->dataImportExistingRows = Data::query()
            ->where('project_id', $project->id)
            ->count();
        $this->reset('dataImportFile');
        $this->resetValidation('dataImportFile');
        $this->dispatch('open-modal', 'import-project-data');
    }

    public function closeDataImportModal(): void
    {
        if ($this->dataImportFile instanceof TemporaryUploadedFile) {
            $this->dataImportFile->delete();
        }

        $this->reset([
            'dataImportFile',
            'dataImportProjectId',
            'dataImportProjectName',
            'dataImportProjectCode',
            'dataImportExistingRows',
        ]);
        $this->resetValidation('dataImportFile');
        $this->dispatch('close-modal', 'import-project-data');
    }

    public function importProjectData(ProjectDataExcelImporter $importer): void
    {
        abort_unless($this->dataImportProjectId, 404);
        $project = $this->authorizedDocumentProject($this->dataImportProjectId);

        $this->dataImportExistingRows = Data::query()
            ->where('project_id', $project->id)
            ->count();

        if ($this->dataImportExistingRows > 0) {
            $this->addError(
                'dataImportFile',
                'Delete the existing project data before importing another Excel file.'
            );

            return;
        }

        $this->validate([
            'dataImportFile' => ['required', 'file', 'extensions:xlsx,xls', 'max:20480'],
        ], [
            'dataImportFile.extensions' => 'Select an Excel file in .xlsx or .xls format.',
        ]);

        $imported = $importer->import($project, $this->dataImportFile->getRealPath());

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch(
            'alert',
            type: 'success',
            title: "{$imported} data rows imported",
            position: 'center',
            timer: 2400
        );
        $this->closeDataImportModal();
    }

    public function deleteImportedProjectData(): void
    {
        abort_unless($this->dataImportProjectId, 404);
        $project = $this->authorizedDocumentProject($this->dataImportProjectId);

        DB::transaction(function () use ($project): void {
            Data::query()->where('project_id', $project->id)->delete();
            $project->update(['data_uploaded' => false]);
        });

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Project data deleted',
            position: 'center',
            timer: 2000
        );
        $this->closeDataImportModal();
    }

    /**
     * Recibe la búsqueda desde Actions.
     */
    #[On('project-search-updated')]
    public function updateSearch(string $search = ''): void
    {
        $this->search = trim($search);

        $this->resetPage();
    }

    /**
     * Recibe la cantidad de registros desde Actions.
     */
    #[On('project-per-page-updated')]
    public function updatePerPage(int|string $perPage = 10): void
    {
        $allowedValues = [5, 10, 20, 50, 100];

        $perPage = (int) $perPage;

        $this->perPage = in_array($perPage, $allowedValues, true)
            ? $perPage
            : 10;

        $this->resetPage();
    }

    /**
     * Recibe todos los filtros desde Filters.
     *
     * @param array<int, string> $plantFilter
     */
    #[On('project-filters-updated')]
    public function updateFilters(
        array $plantFilter = [],
        array $yearSearch = [],
        array $stateSearch = [],
        array $typeOfProjectSearch = [],
        array $investmentFilter = [],
        bool $orderByProject = false
    ): void {
        $allowedPlants = auth()->user()?->availableCompanyCodes() ?? [];

        $this->plantFilter = array_values(
            array_intersect(
                $plantFilter,
                $allowedPlants
            )
        );

        $this->yearSearch = $yearSearch;
        $this->stateSearch = $stateSearch;
        $this->typeOfProjectSearch = $typeOfProjectSearch;
        $this->investmentFilter = $investmentFilter;
        $this->orderByProject = $orderByProject;

        $this->resetPage();
    }

    /**
     * Reinicia la tabla cuando Actions ejecuta resetAll.
     */
    #[On('project-reset-all')]
    public function resetAll(): void
    {
        $this->search = '';
        $this->perPage = 10;

        $this->yearSearch = [];
        $this->stateSearch = [];
        $this->typeOfProjectSearch = [];
        $this->investmentFilter = [];
        $this->plantFilter = [];

        $this->orderByProject = false;

        $this->sortBy = 'id';
        $this->sortDir = 'DESC';

        $this->resetPage();
        $this->dispatchTableState();
    }

    /**
     * Configura la columna de ordenamiento.
     */
    public function setSortBy(string $sortByField): void
    {
        if (!in_array($sortByField, $this->sortableColumns, true)) {
            return;
        }

        if ($this->sortBy === $sortByField) {
            $this->sortDir = $this->sortDir === 'ASC'
                ? 'DESC'
                : 'ASC';
        } else {
            $this->sortBy = $sortByField;
            $this->sortDir = 'DESC';
        }

        /*
        |--------------------------------------------------------------------------
        | Orden normal
        |--------------------------------------------------------------------------
        |
        | Al ordenar mediante una columna de la tabla, se desactiva el orden
        | especial por restante.
        |
        */

        $this->orderByProject = false;

        $this->resetPage();
        $this->dispatchTableState();
    }

    private function dispatchTableState(): void
    {
        $this->dispatch(
            'project-table-state-updated',
            visibleColumns: $this->visibleColumns,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
        );
    }

    /**
     * Construye la consulta principal de proyectos.
     */
    private function getProjectsQuery(): Builder
    {
        $user = auth()->user();

        abort_unless($user, 403);

        return Project::query()
            ->with([
                'company:id,company_code,company_name',
                'creator:id,name',
                'responsible:id,name',
            ])
            ->withExists([
                'data as has_orders' => fn (Builder $query): Builder => $query
                    ->whereNotNull('order_no')
                    ->where('order_no', '<>', ''),
            ])
            ->withSum('data as budgeted_euros', 'global_price_euros')
            ->withSum('data as real_euros', 'real_value_euros')
            ->withSum('data as budgeted_dollars', 'global_price')
            ->withSum('data as real_dollars', 'real_value')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::View
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->when(
                $this->search !== '',
                function (Builder $query): void {
                    $query->where(function (Builder $searchQuery): void {
                        $search = '%' . $this->search . '%';

                        $searchQuery
                            ->where('name', 'like', $search)
                            ->orWhere('order', 'like', $search)
                            ->orWhere('pda_code', 'like', $search)
                            ->orWhere('state', 'like', $search)
                            ->orWhere(
                                'classification_of_investments',
                                'like',
                                $search
                            )
                            ->orWhere('investments', 'like', $search)
                            ->orWhere('justification', 'like', $search);
                    });
                }
            )
            ->when(
                $this->yearSearch !== [],
                fn(Builder $query): Builder => $query->where(
                    function (Builder $yearQuery): void {
                        foreach ($this->yearSearch as $year) {
                            $yearQuery->orWhereYear('forecast_start_date', $year);
                        }
                    }
                )
            )
            ->when(
                $this->stateSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'state',
                    $this->stateSearch
                )
            )
            ->when(
                $this->typeOfProjectSearch !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'classification_of_investments',
                    $this->typeOfProjectSearch
                )
            )
            ->when(
                $this->investmentFilter !== [],
                fn(Builder $query): Builder => $query->whereIn(
                    'investments',
                    $this->investmentFilter
                )
            )
            ->when(
                $this->plantFilter !== [],
                fn (Builder $query): Builder => $query->whereHas(
                    'company',
                    fn (Builder $companyQuery): Builder => $companyQuery
                        ->whereIn('company_code', $this->plantFilter)
                )
            );
    }

    /**
     * Aplica el orden especial por restante.
     */
    private function applyRestOrder(Builder $query): void
    {
        $query
            ->where('state', '!=', 'Finished')
            ->where('data_uploaded', true)
            ->whereHas('data')
            ->addSelect([
                'rest' => Data::query()
                    ->selectRaw(
                        'COALESCE(SUM(global_price_euros), 0) '
                        . '- COALESCE(SUM(booked_euros), 0)'
                    )
                    ->whereColumn('project_id', 'projects.id'),
            ])
            ->orderByDesc('rest');
    }

    public function render(): View
    {
        $query = $this->getProjectsQuery();

        if ($this->orderByProject) {
            $this->applyRestOrder($query);
        } elseif ($this->sortBy === 'order') {
            $this->applyNaturalOrder($query, $this->sortDir);
        } else {
            $query->orderBy(
                $this->sortBy,
                $this->sortDir
            );
        }

        $user = auth()->user();

        return view('livewire.project.table', [
            'projects' => $query->paginate($this->perPage),
            'columnOptions' => self::COLUMN_OPTIONS,
            'updateCompanyIds' => $user?->companyIdsForPermission(
                ProjectPermissionEnum::Update
            ) ?? [],
            'deleteCompanyIds' => $user?->companyIdsForPermission(
                ProjectPermissionEnum::Delete
            ) ?? [],
        ]);
    }

    private function applyNaturalOrder(Builder $query, string $direction): void
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $driver = DB::connection()->getDriverName();
        $quotedColumn = $driver === 'mysql' ? '`projects`.`order`' : 'projects."order"';
        $integerType = $driver === 'mysql' ? 'UNSIGNED' : 'INTEGER';

        $query
            ->orderByRaw("CASE WHEN {$quotedColumn} IS NULL THEN 1 ELSE 0 END")
            ->orderByRaw("CAST({$quotedColumn} AS {$integerType}) {$direction}")
            ->orderByRaw("{$quotedColumn} {$direction}");
    }

}
