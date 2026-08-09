<?php

namespace App\Livewire\Task;

use App\Enums\ProjectPermissionEnum;
use App\Livewire\Task\Concerns\InteractsWithTaskColumns;
use App\Livewire\Task\Concerns\ManagesTaskRecords;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use App\Services\Task\TaskTableQueryService;
use App\Support\Task\TaskTableDefinition;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TaskTable extends Component
{
    use InteractsWithTaskColumns;
    use InteractsWithPerPagePreference;
    use ManagesTaskRecords;
    use WithPagination;

    #[Url]
    public string $search = '';
    public int $perPage = 10;
    public string $sortBy = 'id';
    public string $sortDir = 'DESC';
    public array $statusFilter = [];
    public array $order_numberFilter = [];
    public array $pda_codeFilter = [];
    public array $supplierFilter = [];
    public array $yearSearch = [];
    public array $supplierOptions = [];
    public array $orderNumberOptions = [];
    public array $pda_Options = [];
    public array $statusOptions = TaskTableDefinition::STATUSES;
    public mixed $years = [];
    public array $visibleColumns = [];
    public ?int $editingDataId = null;
    public array $editData = [];
    public ?int $deletingDataId = null;
    public string $deletingDataLabel = '';

    public function mount(TaskTableQueryService $tasks): void
    {
        abort_unless(auth()->user(), 403);
        $this->loadPerPagePreference();
        $this->visibleColumns = $this->storedColumns();
        $options = $tasks->options();
        $this->supplierOptions = $options['supplierOptions'];
        $this->orderNumberOptions = $options['orderNumberOptions'];
        $this->pda_Options = $options['pdaOptions'];
        $this->years = $options['years'];
    }

    public function setSortBy(string $column): void
    {
        if (! in_array($column, TaskTableDefinition::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'DESC';
        }

        $this->resetPage();
    }

    public function updated(string $property): void
    {
        $filterProperties = [
            'search', 'perPage', 'statusFilter', 'order_numberFilter',
            'pda_codeFilter', 'supplierFilter', 'yearSearch',
        ];

        if (in_array(strtok($property, '.'), $filterProperties, true)) {
            if (strtok($property, '.') === 'perPage') {
                $this->savePerPagePreference($this->perPage);
            }
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search', 'statusFilter', 'order_numberFilter',
            'pda_codeFilter', 'supplierFilter', 'yearSearch',
        ]);
        $this->resetPage();
    }

    public function render(TaskTableQueryService $tasks): View
    {
        $this->sanitizeState();

        $data = $tasks->filtered([
            'search' => trim($this->search),
            'statuses' => $this->statusFilter,
            'orders' => $this->order_numberFilter,
            'projects' => $this->pda_codeFilter,
            'suppliers' => $this->supplierFilter,
            'years' => $this->yearSearch,
        ])->with('project')->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        $data->through(function ($item) {
            $item->pda_code = $item->project?->pda_code;
            return $item;
        });

        $user = auth()->user();

        return view('livewire.task.task-table', [
            'data' => $data,
            'columnOptions' => TaskTableDefinition::COLUMN_OPTIONS,
            'updateCompanyIds' => $user->companyIdsForPermission(ProjectPermissionEnum::Update),
            'deleteCompanyIds' => $user->companyIdsForPermission(ProjectPermissionEnum::Delete),
        ])->layout('layouts.app');
    }

    private function sanitizeState(): void
    {
        $this->statusFilter = array_values(array_intersect(
            TaskTableDefinition::STATUSES, $this->statusFilter
        ));
        $this->perPage = in_array($this->perPage, [5, 10, 20, 50, 100], true)
            ? $this->perPage : 10;
        $this->sortBy = in_array($this->sortBy, TaskTableDefinition::SORTABLE_COLUMNS, true)
            ? $this->sortBy : 'id';
        $this->sortDir = strtoupper($this->sortDir) === 'ASC' ? 'ASC' : 'DESC';
    }
}
