<?php

namespace App\Livewire\Data;

use App\Enums\ProjectPermissionEnum;
use App\Exports\ProjectDataExport;
use App\Models\Data;
use App\Models\Project;
use App\Models\UserPreference;
use App\Validation\DataCreateValidation;
use App\Validation\DataUpdateValidation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataTable extends Component
{
    use WithPagination;

    // Versioned because ID became a selectable default column. Existing users
    // should receive the new default once, then remain free to customize it.
    private const PREFERENCE_KEY = 'data.table.visible_columns.v2';

    private const COLUMN_OPTIONS = [
        'id' => 'ID',
        'area' => 'Area',
        'group_1' => 'Group 1',
        'group_2' => 'Group 2',
        'description' => 'Description',
        'general_classification' => 'Classification',
        'item_type' => 'Item type',
        'unit' => 'Unit',
        'qty' => 'Qty',
        'unit_price' => 'Unit price',
        'global_price' => 'Budgeted $',
        'global_price_euros' => 'Budgeted €',
        'stage' => 'Stage',
        'real_value' => 'Real $',
        'real_value_euros' => 'Real €',
        'percentage' => 'Percentage',
        'executed_dollars' => 'Executed $',
        'executed_euros' => 'Executed €',
        'booked' => 'Booked $',
        'booked_euros' => 'Booked €',
        'supplier' => 'Supplier',
        'code' => 'Code',
        'order_no' => 'Order no.',
        'input_num' => 'Input no.',
        'observations' => 'Observations',
        'actions' => 'Actions',
    ];

    private const DEFAULT_COLUMNS = [
        'id',
        'area',
        'group_1',
        'description',
        'general_classification',
        'qty',
        'global_price_euros',
        'stage',
        'real_value_euros',
        'executed_euros',
        'supplier',
        'order_no',
        'observations',
        'actions',
    ];

    private const NUMERIC_COLUMNS = [
        'qty',
        'unit_price',
        'global_price',
        'global_price_euros',
        'real_value',
        'real_value_euros',
        'percentage',
        'executed_dollars',
        'executed_euros',
        'booked',
        'booked_euros',
    ];

    private const DOLLAR_TO_EURO_COLUMNS = [
        'global_price' => 'global_price_euros',
        'real_value' => 'real_value_euros',
        'executed_dollars' => 'executed_euros',
        'booked' => 'booked_euros',
    ];

    private const FILTER_COLUMNS = [
        'areaFilter' => 'area',
        'classificationFilter' => 'general_classification',
        'itemTypeFilter' => 'item_type',
        'stageFilter' => 'stage',
        'supplierFilter' => 'supplier',
    ];

    public Project $project;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 10)]
    public int $perPage = 10;

    #[Url(except: 'id')]
    public string $sortBy = 'id';

    #[Url(except: 'desc')]
    public string $sortDir = 'desc';

    /** @var array<int, string> */
    public array $visibleColumns = [];

    /** @var array<int, string> */
    #[Url(as: 'area', except: [])]
    public array $areaFilter = [];

    /** @var array<int, string> */
    #[Url(as: 'classification', except: [])]
    public array $classificationFilter = [];

    /** @var array<int, string> */
    #[Url(as: 'item_type', except: [])]
    public array $itemTypeFilter = [];

    /** @var array<int, string> */
    #[Url(as: 'stage', except: [])]
    public array $stageFilter = [];

    /** @var array<int, string> */
    #[Url(as: 'supplier', except: [])]
    public array $supplierFilter = [];

    public ?int $editingDataId = null;

    public array $editData = [];

    public string $bookedBase = '0.00';

    public string $bookedMultiplier = '1.000000';

    public bool $creatingData = false;

    public ?int $deletingDataId = null;

    public string $deletingDataLabel = '';

    public function mount(Project $project): void
    {
        $user = auth()->user();
        abort_unless(
            $user?->hasPermissionInCompany(
                ProjectPermissionEnum::View,
                (int) $project->company_id
            ),
            403
        );

        $this->project = $project->load('company:id,company_name,company_code,multiplier');
        $this->visibleColumns = $this->storedColumns();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(int|string $value): void
    {
        $allowed = [5, 10, 20, 50, 100];
        $this->perPage = in_array((int) $value, $allowed, true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    #[Renderless]
    public function exportExcel(): BinaryFileResponse
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Export);

        $rows = $this->filteredDataQuery()
            ->orderBy($this->sortBy, $this->sortDir)
            ->get();

        return (new ProjectDataExport())->download(
            $this->project,
            $rows,
            array_values(array_diff($this->visibleColumns, ['actions']))
        );
    }

    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);

        UserPreference::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'key' => self::PREFERENCE_KEY,
            ],
            ['value' => $this->visibleColumns]
        );
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = self::DEFAULT_COLUMNS;
        $this->updatedVisibleColumns();
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

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'areaFilter',
            'classificationFilter',
            'itemTypeFilter',
            'stageFilter',
            'supplierFilter',
        ]);
        $this->resetPage();
    }

    public function openEditModal(int $dataId): void
    {
        $data = $this->authorizedData($dataId, ProjectPermissionEnum::Update);

        $this->editingDataId = (int) $data->id;
        $this->creatingData = false;
        $this->editData = $data->only(
            array_values(array_diff(array_keys(self::COLUMN_OPTIONS), ['actions']))
        );
        $this->initializeBookedCalculator();
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-project-data');
    }

    public function openCreateModal(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Update);

        $this->editingDataId = null;
        $this->creatingData = true;
        $this->editData = collect(array_keys(self::COLUMN_OPTIONS))
            ->reject(fn(string $column) => $column === 'actions')
            ->mapWithKeys(fn(string $column) => [
                $column => in_array($column, self::NUMERIC_COLUMNS, true) ? 0 : null,
            ])
            ->all();
        $this->initializeBookedCalculator();
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-project-data');
    }

    public function closeEditModal(): void
    {
        $this->reset([
            'editingDataId',
            'editData',
            'creatingData',
            'bookedBase',
            'bookedMultiplier',
        ]);
        $this->resetValidation();
        $this->dispatch('close-modal', 'edit-project-data');
    }

    public function createData(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Update);
        abort_unless($this->creatingData, 409);

        if (! $this->synchronizeEuroValues()) {
            return;
        }

        $validated = $this->validate(
            DataCreateValidation::rules(),
            [],
            DataCreateValidation::attributes()
        );

        Data::query()->create([
            'project_id' => $this->project->id,
            ...$validated['editData'],
        ]);
        $this->project->update(['data_uploaded' => true]);

        $this->closeEditModal();
        $this->resetPage();
        $this->dispatch('alert', type: 'success', title: 'Data row created', position: 'center', timer: 1800);
    }

    public function updateData(): void
    {
        abort_unless($this->editingDataId, 404);
        $data = $this->authorizedData($this->editingDataId, ProjectPermissionEnum::Update);

        if (! $this->synchronizeEuroValues()) {
            return;
        }

        $validated = $this->validate(
            DataUpdateValidation::rules(),
            [],
            DataUpdateValidation::attributes()
        );

        $data->update($validated['editData']);
        $this->closeEditModal();
        $this->dispatch('alert', type: 'success', title: 'Data updated', position: 'center', timer: 1800);
    }

    public function updatedEditData(mixed $value, string $field): void
    {
        $euroField = self::DOLLAR_TO_EURO_COLUMNS[$field] ?? null;
        $rate = (float) $this->project->rate;

        if ($euroField === null || ! is_numeric($value)) {
            return;
        }

        if ($rate <= 0) {
            $this->addError(
                "editData.{$field}",
                'The project rate must be greater than zero to convert currencies.'
            );

            return;
        }

        $this->resetValidation("editData.{$field}");

        $this->editData[$euroField] = round((float) $value / $rate, 2);
    }

    private function initializeBookedCalculator(): void
    {
        $multiplier = max((float) ($this->project->company?->multiplier ?? 1), 0);

        $this->bookedMultiplier = number_format($multiplier, 6, '.', '');
        $this->bookedBase = '0.00';
    }

    public function openDeleteModal(int $dataId): void
    {
        $data = $this->authorizedData($dataId, ProjectPermissionEnum::Delete);

        $this->deletingDataId = (int) $data->id;
        $this->deletingDataLabel = $data->description ?: $data->code ?: "Record #{$data->id}";
        $this->dispatch('open-modal', 'delete-project-data');
    }

    public function closeDeleteModal(): void
    {
        $this->reset(['deletingDataId', 'deletingDataLabel']);
        $this->dispatch('close-modal', 'delete-project-data');
    }

    public function deleteData(): void
    {
        abort_unless($this->deletingDataId, 404);
        $data = $this->authorizedData($this->deletingDataId, ProjectPermissionEnum::Delete);
        $data->delete();

        if (! Data::query()->where('project_id', $this->project->id)->exists()) {
            $this->project->update(['data_uploaded' => false]);
        }

        $this->closeDeleteModal();
        $this->resetPage();
        $this->dispatch('alert', type: 'success', title: 'Data deleted', position: 'center', timer: 1800);
    }

    public function setSortBy(string $column): void
    {
        if ($column === 'actions' || ! array_key_exists($column, self::COLUMN_OPTIONS)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function render(): View
    {
        if ($this->sortBy === 'actions' || ! array_key_exists($this->sortBy, self::COLUMN_OPTIONS)) {
            $this->sortBy = 'id';
        }

        if (! in_array($this->sortDir, ['asc', 'desc'], true)) {
            $this->sortDir = 'desc';
        }

        $term = trim($this->search);

        $data = $this->filteredDataQuery()
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        $filterOptions = collect(self::FILTER_COLUMNS)
            ->mapWithKeys(fn(string $column, string $filter) => [
                $filter => Data::query()
                    ->where('project_id', $this->project->id)
                    ->whereNotNull($column)
                    ->where($column, '<>', '')
                    ->distinct()
                    ->orderBy($column)
                    ->pluck($column)
                    ->map(fn($value) => ['value' => (string) $value, 'label' => (string) $value])
                    ->values(),
            ]);

        return view('livewire.data.data-table', [
            'data' => $data,
            'columnOptions' => self::COLUMN_OPTIONS,
            'filterOptions' => $filterOptions,
            'numericColumns' => self::NUMERIC_COLUMNS,
            'canEditData' => auth()->user()?->hasPermissionInCompany(
                ProjectPermissionEnum::Update,
                (int) $this->project->company_id
            ) ?? false,
            'canDeleteData' => auth()->user()?->hasPermissionInCompany(
                ProjectPermissionEnum::Delete,
                (int) $this->project->company_id
            ) ?? false,
            'canExportData' => auth()->user()?->hasPermissionInCompany(
                ProjectPermissionEnum::Export,
                (int) $this->project->company_id
            ) ?? false,
            'hasOrders' => Data::query()
                ->where('project_id', $this->project->id)
                ->whereNotNull('order_no')
                ->where('order_no', '<>', '')
                ->exists(),
            'hasActiveFilters' => $term !== ''
                || collect(self::FILTER_COLUMNS)
                ->keys()
                ->contains(fn(string $property) => $this->{$property} !== []),
        ]);
    }

    private function filteredDataQuery()
    {
        $term = trim($this->search);

        return Data::query()
            ->where('project_id', $this->project->id)
            ->when($term !== '', function ($query) use ($term): void {
                $query->where(function ($query) use ($term): void {
                    foreach (
                        [
                            'area',
                            'group_1',
                            'group_2',
                            'description',
                            'general_classification',
                            'item_type',
                            'stage',
                            'supplier',
                            'code',
                            'order_no',
                            'input_num',
                            'observations',
                        ] as $column
                    ) {
                        $query->orWhere($column, 'like', "%{$term}%");
                    }
                });
            })
            ->when($this->areaFilter !== [], fn($query) => $query->whereIn('area', $this->areaFilter))
            ->when(
                $this->classificationFilter !== [],
                fn($query) => $query->whereIn('general_classification', $this->classificationFilter)
            )
            ->when($this->itemTypeFilter !== [], fn($query) => $query->whereIn('item_type', $this->itemTypeFilter))
            ->when($this->stageFilter !== [], fn($query) => $query->whereIn('stage', $this->stageFilter))
            ->when($this->supplierFilter !== [], fn($query) => $query->whereIn('supplier', $this->supplierFilter));
    }

    private function authorizedData(int $dataId, ProjectPermissionEnum $permission): Data
    {
        $this->authorizeProjectData($permission);

        return Data::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($dataId);
    }

    private function authorizeProjectData(ProjectPermissionEnum $permission): void
    {
        abort_unless(
            auth()->user()?->hasPermissionInCompany(
                $permission,
                (int) $this->project->company_id
            ),
            403
        );
    }

    private function synchronizeEuroValues(): bool
    {
        $rate = (float) $this->project->rate;
        $hasDollarValue = collect(array_keys(self::DOLLAR_TO_EURO_COLUMNS))
            ->contains(fn(string $field) => abs((float) ($this->editData[$field] ?? 0)) > 0);

        if ($rate <= 0 && $hasDollarValue) {
            $this->addError(
                'editData.global_price',
                'The project rate must be greater than zero to convert dollars to euros.'
            );

            return false;
        }

        foreach (self::DOLLAR_TO_EURO_COLUMNS as $dollarField => $euroField) {
            $this->editData[$euroField] = $rate > 0
                ? round((float) ($this->editData[$dollarField] ?? 0) / $rate, 2)
                : 0;
        }

        return true;
    }

    /** @return array<int, string> */
    private function storedColumns(): array
    {
        $stored = UserPreference::query()
            ->where('user_id', auth()->id())
            ->where('key', self::PREFERENCE_KEY)
            ->first()?->value;

        return $this->sanitizeColumns(is_array($stored) ? $stored : self::DEFAULT_COLUMNS);
    }

    /** @param array<int, mixed> $columns
     *  @return array<int, string>
     */
    private function sanitizeColumns(array $columns): array
    {
        $selected = array_values(array_intersect(array_keys(self::COLUMN_OPTIONS), $columns));

        $selected = array_values(array_diff($selected, ['actions']));
        $selected[] = 'actions';

        return count($selected) > 1 ? $selected : self::DEFAULT_COLUMNS;
    }
}
