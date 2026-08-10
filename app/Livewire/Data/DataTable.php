<?php

namespace App\Livewire\Data;

use App\Enums\ProjectPermissionEnum;
use App\Exports\ProjectDataExport;
use App\Exports\ProjectDataImportExport;
use App\Livewire\Data\Concerns\AuthorizesProjectData;
use App\Livewire\Data\Concerns\ConvertsDataCurrencies;
use App\Livewire\Data\Concerns\InteractsWithDataColumns;
use App\Livewire\Data\Concerns\InteractsWithDataFilters;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use App\Livewire\Data\Concerns\ManagesDataRecords;
use App\Models\Project;
use App\Services\Data\DataTableQueryService;
use App\Support\Data\DataTableDefinition;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataTable extends Component
{
    use AuthorizesProjectData;
    use ConvertsDataCurrencies;
    use InteractsWithDataColumns;
    use InteractsWithDataFilters;
    use InteractsWithPerPagePreference;
    use ManagesDataRecords;
    use WithPagination;

    public Project $project;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 10)]
    public int $perPage = 10;

    #[Url(except: 'id')]
    public string $sortBy = 'id';

    #[Url(except: 'desc')]
    public string $sortDir = 'desc';

    public array $visibleColumns = [];

    #[Url(as: 'area', except: [])]
    public array $areaFilter = [];

    #[Url(as: 'classification', except: [])]
    public array $classificationFilter = [];

    #[Url(as: 'item_type', except: [])]
    public array $itemTypeFilter = [];

    #[Url(as: 'stage', except: [])]
    public array $stageFilter = [];

    #[Url(as: 'supplier', except: [])]
    public array $supplierFilter = [];

    #[Url(as: 'order_year', except: [])]
    public array $orderYearFilter = [];

    public ?int $editingDataId = null;
    public array $editData = [];
    public string $bookedBase = '0.00';
    public string $bookedMultiplier = '1.000000';
    public bool $creatingData = false;
    public ?int $deletingDataId = null;
    public string $deletingDataLabel = '';

    public function mount(Project $project): void
    {
        $this->loadPerPagePreference();
        $user = auth()->user();

        abort_unless(
            $user?->hasPermissionInCompany(
                ProjectPermissionEnum::View,
                (int) $project->company_id
            ),
            403
        );

        $this->project = $project->load(
            'company:id,company_name,company_code,multiplier'
        );

        $this->visibleColumns =
            $this->storedColumns();
    }

    #[Renderless]
    public function exportExcel(): BinaryFileResponse
    {
        $this->authorizeProjectData(
            ProjectPermissionEnum::Export
        );

        $rows = $this->queryService()
            ->filtered(
                $this->project,
                $this->search,
                $this->activeFilters()
            )
            ->orderBy(
                $this->sortBy,
                $this->sortDir
            )
            ->get();

        return (new ProjectDataExport())
            ->download(
                $this->project,
                $rows,
                array_values(
                    array_diff(
                        $this->visibleColumns,
                        ['actions']
                    )
                )
            );
    }

    #[Renderless]
    public function exportImportReadyExcel(): BinaryFileResponse
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Export);

        return (new ProjectDataImportExport())->download(
            $this->project,
            $this->project->data()->orderBy('id')->get()
        );
    }

    public function render(): View
    {
        $this->sanitizeSort();

        $service = $this->queryService();

        $data = $service
            ->filtered(
                $this->project,
                $this->search,
                $this->activeFilters()
            )
            ->orderBy(
                $this->sortBy,
                $this->sortDir
            )
            ->paginate(
                $this->perPage
            );

        return view(
            'livewire.data.data-table',
            [
                'data' => $data,

                'columnOptions' =>
                    DataTableDefinition::COLUMN_OPTIONS,

                'filterOptions' =>
                    $service->filterOptions(
                        $this->project
                    ),

                'numericColumns' =>
                    DataTableDefinition::NUMERIC_COLUMNS,

                'linkedCurrencyColumns' =>
                    DataTableDefinition::LINKED_CURRENCY_COLUMNS,

                'derivedEuroColumns' =>
                    DataTableDefinition::DERIVED_EURO_COLUMNS,

                'canEditData' =>
                    $this->can(
                        ProjectPermissionEnum::Update
                    ),

                'canDeleteData' =>
                    $this->can(
                        ProjectPermissionEnum::Delete
                    ),

                'canExportData' =>
                    $this->can(
                        ProjectPermissionEnum::Export
                    ),

                'hasOrders' =>
                    $service->hasOrders(
                        $this->project
                    ),

                'hasActiveFilters' =>
                    $this->hasActiveFilters(),
            ]
        );
    }

    private function queryService(): DataTableQueryService
    {
        return app(
            DataTableQueryService::class
        );
    }
}
