<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Services\ProjectDataExcelImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ProjectDataImportManager extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public bool $deleteConfirmation = false;
    public mixed $file = null;
    public ?int $projectId = null;
    public string $projectName = '';
    public string $projectCode = '';
    public int $existingRows = 0;

    #[On('open-project-data-import-manager')]
    public function open(int $projectId, bool $confirmDelete = false): void
    {
        $project = $this->authorizedProject($projectId);
        $this->projectId = (int) $project->id;
        $this->projectName = $project->name;
        $this->projectCode = $project->pda_code;
        $this->existingRows = Data::query()->where('project_id', $project->id)->count();
        $this->deleteConfirmation = $confirmDelete && $this->existingRows > 0;
        $this->reset('file');
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['file', 'projectId', 'projectName', 'projectCode', 'existingRows', 'deleteConfirmation']);
        $this->resetValidation();
    }

    public function importProjectDataWorkbook(ProjectDataExcelImporter $importer): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        $this->existingRows = Data::query()->where('project_id', $project->id)->count();
        if ($this->existingRows > 0) {
            $this->addError('file', 'Delete the existing imported data rows before importing another workbook.');
            return;
        }

        $this->validate([
            'file' => ['required', 'file', 'extensions:xlsx,xls', 'mimes:xlsx,xls', 'max:20480'],
        ], [
            'file.required' => 'Select the completed project data workbook.',
            'file.extensions' => 'Select an Excel file in .xlsx or .xls format.',
            'file.mimes' => 'The selected file is not a valid Excel workbook.',
            'file.max' => 'The workbook may not be larger than 20 MB.',
        ]);

        try {
            $imported = $importer->import($project, $this->file->getRealPath());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('file', 'The workbook could not be read. Verify that it is not damaged and uses the approved template.');
            return;
        }

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: "{$imported} project data rows imported", position: 'center', timer: 2200);
        $this->close();
    }

    public function deleteImportedRows(): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        DB::transaction(function () use ($project): void {
            Data::query()->where('project_id', $project->id)->delete();
            $project->update(['data_uploaded' => false]);
        });
        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: 'Imported project data deleted', position: 'center', timer: 1800);
        $this->close();
    }

    private function authorizedProject(int $projectId): Project
    {
        $user = auth()->user();
        abort_unless($user, 403);
        return Project::query()->whereIn('company_id', $user->companiesForPermissionQuery(ProjectPermissionEnum::Update)
            ->select('companies.id')->reorder())->findOrFail($projectId);
    }

    public function render(): View
    {
        return view('livewire.project.project-data-import-manager');
    }
}
