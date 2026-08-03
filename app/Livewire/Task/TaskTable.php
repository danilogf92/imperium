<?php

namespace App\Livewire\Task;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Models\UserPreference;
use App\Validation\TaskDataUpdateValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TaskTable extends Component
{
    use WithPagination;

    private const PREFERENCE_KEY = 'tasks.table.visible_columns';

    private const COLUMN_OPTIONS = [
        'pda_code' => 'PDA code',
        'description' => 'Description',
        'qty' => 'Qty',
        'real_value' => 'Real value $',
        'global_price' => 'Global price $',
        'booked' => 'Booked $',
        'percentage' => 'Percentage',
        'supplier' => 'Supplier',
        'order_no' => 'Order no.',
        'actions' => 'Actions',
    ];

    private const DEFAULT_COLUMNS = [
        'pda_code',
        'description',
        'qty',
        'real_value',
        'global_price',
        'booked',
        'percentage',
        'supplier',
        'order_no',
        'actions',
    ];

    public $active = true;

    #[Url()]
    public $search = '';
    public $perPage = 10;

    public $sortBy = 'id';
    public $sortDir = 'DESC';
    public $auxSearch = "";
    
    public array $statusFilter = [];
    public array $order_numberFilter = [];
    public array $pda_codeFilter = [];
    public array $supplierFilter = [];
    
    public $supplierOptions = [];
    public $orderNumberOptions = [];
    public $pda_Options = [];
    public $statusOptions = ['completed', 'progress', 'pending'];
    public $years;
    public array $yearSearch = [];

    public array $visibleColumns = [];

    public ?int $editingDataId = null;

    public array $editData = [];

    public ?int $deletingDataId = null;

    public string $deletingDataLabel = '';
    
    public function mount(): void
    {
        abort_unless(auth()->user(), 403);

        $this->years = $this->getYears();
        $this->visibleColumns = $this->storedColumns();

        $this->supplierOptions = $this->accessibleDataQuery()
            ->whereNotNull('supplier')
            ->where('supplier', '<>', '')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier')
            ->toArray();

        $this->orderNumberOptions = $this->accessibleDataQuery()
            ->whereNotNull('order_no')
            ->where('order_no', '<>', '')
            ->distinct()
            ->orderBy('order_no')
            ->pluck('order_no')
            ->toArray();

        $this->pda_Options = $this->accessibleProjectsQuery()
            ->whereNotNull('pda_code')
            ->where('pda_code', '<>', '') 
            ->distinct()
            ->orderBy('pda_code')
            ->pluck('pda_code')
            ->toArray();
    }

    public function setSortBy($sortByField)
    {
        if (
            in_array($sortByField, ['pda_code', 'actions'], true)
            || ! array_key_exists($sortByField, self::COLUMN_OPTIONS)
        ) {
            return;
        }

        if ($this->sortBy === $sortByField) 
        {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : 'ASC';
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }
    
    public function updated($property, $value)
    {
        $rootProperty = explode('.', (string) $property, 2)[0];

        if (in_array($rootProperty, [
            'search',
            'perPage',
            'statusFilter',
            'order_numberFilter',
            'pda_codeFilter',
            'supplierFilter',
            'yearSearch',
        ], true)) {
            $this->resetPage();
        }
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->order_numberFilter = [];
        $this->pda_codeFilter = [];
        $this->supplierFilter = [];
        $this->yearSearch = [];
        $this->resetPage();
    }

    public function updatedVisibleColumns(): void
    {
        $this->visibleColumns = $this->sanitizeColumns($this->visibleColumns);

        UserPreference::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'key' => self::PREFERENCE_KEY],
            ['value' => $this->visibleColumns]
        );
    }

    public function resetColumns(): void
    {
        $this->visibleColumns = self::DEFAULT_COLUMNS;
        $this->updatedVisibleColumns();
    }

    public function openEditModal(int $dataId): void
    {
        $data = $this->authorizedData($dataId, ProjectPermissionEnum::Update);
        $this->editingDataId = (int) $data->id;
        $this->editData = [
            'percentage' => (int) $data->percentage,
        ];
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-task-data');
    }

    public function closeEditModal(): void
    {
        $this->reset(['editingDataId', 'editData']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'edit-task-data');
    }

    public function updateData(): void
    {
        abort_unless($this->editingDataId, 404);
        $data = $this->authorizedData($this->editingDataId, ProjectPermissionEnum::Update);
        $validated = $this->validate(
            TaskDataUpdateValidation::rules(),
            [],
            TaskDataUpdateValidation::attributes()
        );

        $data->update([
            'percentage' => $validated['editData']['percentage'],
        ]);
        $this->closeEditModal();
        $this->dispatch('alert', type: 'success', title: 'Task updated', position: 'center', timer: 1800);
    }

    public function openDeleteModal(int $dataId): void
    {
        $data = $this->authorizedData($dataId, ProjectPermissionEnum::Delete);
        $this->deletingDataId = (int) $data->id;
        $this->deletingDataLabel = $data->description ?: "Task #{$data->id}";
        $this->dispatch('open-modal', 'delete-task-data');
    }

    public function closeDeleteModal(): void
    {
        $this->reset(['deletingDataId', 'deletingDataLabel']);
        $this->dispatch('close-modal', 'delete-task-data');
    }

    public function deleteData(): void
    {
        abort_unless($this->deletingDataId, 404);
        $data = $this->authorizedData($this->deletingDataId, ProjectPermissionEnum::Delete);
        $projectId = (int) $data->project_id;
        $data->delete();

        if (! Data::query()->where('project_id', $projectId)->exists()) {
            Project::query()->whereKey($projectId)->update(['data_uploaded' => false]);
        }

        $this->closeDeleteModal();
        $this->resetPage();
        $this->dispatch('alert', type: 'success', title: 'Task deleted', position: 'center', timer: 1800);
    }
    
    public function getYears()
    {
        $uniqueYears = $this->accessibleProjectsQuery()
            ->whereNotNull('forecast_start_date')
            ->distinct()
            ->get(['forecast_start_date'])
            ->pluck('forecast_start_date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y');
            })
            ->unique()
            ->sortDesc()
            ->values();
    
        return $uniqueYears;
    }

    public function render(): View
    {
        $this->statusFilter = array_values(array_intersect(
            $this->statusOptions,
            $this->statusFilter
        ));

        if (
            in_array($this->sortBy, ['pda_code', 'actions'], true)
            || ! array_key_exists($this->sortBy, self::COLUMN_OPTIONS)
        ) {
            $this->sortBy = 'id';
        }

        if (! in_array(strtoupper($this->sortDir), ['ASC', 'DESC'], true)) {
            $this->sortDir = 'DESC';
        }

        $dataQuery = $this->accessibleDataQuery();
        
        if ($this->search) {
            $this->auxSearch = trim($this->search);
            
            $dataQuery->where(function ($query) {
                $query->where('description', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('qty', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('real_value', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('global_price', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('booked', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('percentage', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhere('order_no', 'LIKE', '%' . $this->auxSearch . '%')
                    ->orWhereHas('project', function ($query) {
                        $query->where('pda_code', 'LIKE', '%' . $this->auxSearch . '%');
                    });
            });
        }
        
        if ($this->statusFilter !== []) {
            $dataQuery->where(function (Builder $query): void {
                foreach ($this->statusFilter as $status) {
                    $query->orWhere(function (Builder $query) use ($status): void {
                        if ($status === 'completed') {
                            $query->where('percentage', 100)->whereNotNull('supplier');
                        } elseif ($status === 'progress') {
                            $query->whereBetween('percentage', [0, 99])->whereNotNull('supplier');
                        } elseif ($status === 'pending') {
                            $query->whereNull('supplier');
                        }
                    });
                }
            });
        }
        
        if ($this->order_numberFilter !== []) {
            $dataQuery->whereIn('order_no', $this->order_numberFilter);
        }
        
        if ($this->supplierFilter !== []) {
            $dataQuery->whereIn('supplier', $this->supplierFilter);
        }
    
        if ($this->pda_codeFilter !== []) {
            $dataQuery->whereHas('project', function (Builder $query): void {
                $query->whereIn('pda_code', $this->pda_codeFilter);
            });
        }
        
        if ($this->yearSearch !== []) {
            $dataQuery->whereHas('project', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    foreach ($this->yearSearch as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            });
        }
    
        $dataQuery->orderBy($this->sortBy, $this->sortDir);
        $data = $dataQuery->with('project')->paginate($this->perPage); 
        
        $data->transform(function ($item) {
            $item->pda_code = $item->project?->pda_code;
            return $item;
        });
    
        return view('livewire.task.task-table', [
            'data' => $data,
            'columnOptions' => self::COLUMN_OPTIONS,
            'updateCompanyIds' => auth()->user()
                ->companiesForPermission(ProjectPermissionEnum::Update)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all(),
            'deleteCompanyIds' => auth()->user()
                ->companiesForPermission(ProjectPermissionEnum::Delete)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ])->layout('layouts.app');
    }

    private function authorizedData(int $dataId, ProjectPermissionEnum $permission): Data
    {
        $data = Data::query()->with('project')->findOrFail($dataId);

        abort_unless(
            auth()->user()?->hasPermissionInCompany(
                $permission,
                (int) $data->project->company_id
            ),
            403
        );

        return $data;
    }

    private function storedColumns(): array
    {
        $stored = UserPreference::query()
            ->where('user_id', auth()->id())
            ->where('key', self::PREFERENCE_KEY)
            ->first()?->value;

        return $this->sanitizeColumns(is_array($stored) ? $stored : self::DEFAULT_COLUMNS);
    }

    private function sanitizeColumns(array $columns): array
    {
        $selected = array_values(array_intersect(array_keys(self::COLUMN_OPTIONS), $columns));

        $selected = array_values(array_diff($selected, ['actions']));
        $selected[] = 'actions';

        return count($selected) > 1 ? $selected : self::DEFAULT_COLUMNS;
    }

    private function accessibleDataQuery(): Builder
    {
        return Data::query()->whereHas(
            'project',
            fn (Builder $query) => $query->whereIn(
                'company_id',
                auth()->user()
                    ->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            )
        );
    }

    private function accessibleProjectsQuery(): Builder
    {
        return Project::query()->whereIn(
            'company_id',
            auth()->user()
                ->companiesForPermissionQuery(ProjectPermissionEnum::View)
                ->select('companies.id')
                ->reorder()
        );
    }
}
