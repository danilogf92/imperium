<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Livewire\Project\Concerns\InteractsWithProjectColumns;
use App\Livewire\Project\Concerns\InteractsWithProjectFilters;
use App\Livewire\Project\Concerns\ManagesProjectDataImports;
use App\Livewire\Project\Concerns\ManagesProjectDocuments;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use App\Services\Project\ProjectTableQueryService;
use App\Support\Project\ProjectTableDefinition;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Table extends Component
{
    use InteractsWithProjectColumns;
    use InteractsWithProjectFilters;
    use InteractsWithPerPagePreference;
    use ManagesProjectDataImports;
    use ManagesProjectDocuments;
    use WithFileUploads;
    use WithPagination;

    public bool $active = false;
    public int $perPage = 10;
    public string $search = '';
    public string $sortBy = 'order';
    public string $sortDir = 'ASC';
    public array $yearSearch = [];
    public array $stateSearch = [];
    public array $typeOfProjectSearch = [];
    public array $investmentFilter = [];
    public array $plantFilter = [];
    public array $projectIdeaFilter = [];
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
    public bool $dataDeleteConfirmation = false;

    public function mount(bool $active = false): void
    {
        $this->loadPerPagePreference();
        $this->active = $active;
        $this->visibleColumns = $this->storedVisibleColumns();
        $this->dispatchTableState();
    }

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

    public function render(ProjectTableQueryService $projects): View
    {
        $query = $projects->filtered([
            'search' => $this->search,
            'years' => $this->yearSearch,
            'states' => $this->stateSearch,
            'types' => $this->typeOfProjectSearch,
            'investments' => $this->investmentFilter,
            'plants' => $this->plantFilter,
            'projectIdeas' => $this->projectIdeaFilter,
        ]);
        $projects->applyOrder($query, $this->orderByProject, $this->sortBy, $this->sortDir);
        $user = auth()->user();

        return view('livewire.project.table', [
            'projects' => $query->paginate($this->perPage),
            'columnOptions' => ProjectTableDefinition::COLUMN_OPTIONS,
            'updateCompanyIds' => $user?->companyIdsForPermission(ProjectPermissionEnum::Update) ?? [],
            'deleteCompanyIds' => $user?->companyIdsForPermission(ProjectPermissionEnum::Delete) ?? [],
        ]);
    }
}
