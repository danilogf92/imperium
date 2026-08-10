<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Data;
use App\Services\ProjectDataExcelImporter;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ManagesProjectDataImports
{
    public function openDataImportModal(int $projectId): void
    {
        $project = $this->authorizedMutableProject($projectId);
        $this->dataImportProjectId = (int) $project->id;
        $this->dataImportProjectName = $project->name;
        $this->dataImportProjectCode = $project->pda_code;
        $this->dataImportExistingRows = Data::query()->where('project_id', $project->id)->count();
        $this->dataDeleteConfirmation = false;
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
            'dataImportFile', 'dataImportProjectId', 'dataImportProjectName',
            'dataImportProjectCode', 'dataImportExistingRows', 'dataDeleteConfirmation',
        ]);
        $this->resetValidation('dataImportFile');
        $this->dispatch('close-modal', 'import-project-data');
    }

    public function importProjectData(ProjectDataExcelImporter $importer): void
    {
        abort_unless($this->dataImportProjectId, 404);
        $project = $this->authorizedMutableProject($this->dataImportProjectId);
        $this->dataImportExistingRows = Data::query()->where('project_id', $project->id)->count();
        if ($this->dataImportExistingRows > 0) {
            $this->addError('dataImportFile',
                'Delete the existing project data before importing another Excel file.');
            return;
        }
        $this->validate(
            ['dataImportFile' => ['required', 'file', 'extensions:xlsx,xls', 'max:20480']],
            ['dataImportFile.extensions' => 'Select an Excel file in .xlsx or .xls format.']
        );
        $imported = $importer->import($project, $this->dataImportFile->getRealPath());
        $this->notifyProjectChange($project, "{$imported} data rows imported");
        $this->closeDataImportModal();
    }

    public function deleteImportedProjectData(): void
    {
        abort_unless($this->dataImportProjectId, 404);
        $project = $this->authorizedMutableProject($this->dataImportProjectId);
        DB::transaction(function () use ($project): void {
            Data::query()->where('project_id', $project->id)->delete();
            $project->update(['data_uploaded' => false]);
        });
        $this->notifyProjectChange($project, 'Project data deleted');
        $this->closeDataImportModal();
    }

    public function requestImportedDataDeletion(): void
    {
        abort_unless($this->dataImportProjectId && $this->dataImportExistingRows > 0, 404);
        $this->dataDeleteConfirmation = true;
    }

    public function cancelImportedDataDeletion(): void
    {
        $this->dataDeleteConfirmation = false;
    }
}
