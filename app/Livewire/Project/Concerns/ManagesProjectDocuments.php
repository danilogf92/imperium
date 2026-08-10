<?php

namespace App\Livewire\Project\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Validation\ProjectDocumentUploadValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ManagesProjectDocuments
{
    public function openDocumentModal(int $projectId): void
    {
        $project = $this->authorizedMutableProject($projectId);
        $this->documentProjectId = (int) $project->id;
        $this->documentProjectName = $project->name;
        $this->documentProjectCode = $project->pda_code;
        $this->currentDocumentName = $project->file_name;
        $this->reset('document');
        $this->resetValidation('document');
    }

    public function closeDocumentModal(): void
    {
        $this->reset([
            'document', 'documentProjectId', 'documentProjectName',
            'documentProjectCode', 'currentDocumentName',
        ]);
        $this->resetValidation('document');
        $this->dispatch('close-modal', 'upload-project-document');
    }

    public function uploadDocument(): void
    {
        abort_unless($this->documentProjectId, 404);
        $project = $this->authorizedMutableProject($this->documentProjectId);
        abort_if(filled($project->upload_pda), 409,
            'Delete the current document before uploading another one.');
        $this->validate(
            ProjectDocumentUploadValidation::rules(),
            ProjectDocumentUploadValidation::messages(),
            ProjectDocumentUploadValidation::attributes()
        );

        $originalName = $this->document->getClientOriginalName();
        $extension = strtolower($this->document->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'document';
        $fileName = now()->format('YmdHis').'-'.$baseName.'.'.$extension;
        $path = $this->document->storeAs("projects/{$project->id}/documents", $fileName, 'public');
        $previousPath = $project->upload_pda;
        $project->update(['upload_pda' => $path, 'file_name' => $originalName]);

        if (filled($previousPath) && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/documents/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->notifyProjectChange($project, 'Document uploaded');
        $this->closeDocumentModal();
    }

    public function openDeleteDocumentModal(int $projectId): void
    {
        $project = $this->authorizedMutableProject($projectId);
        abort_if(blank($project->upload_pda), 404);
        $this->deleteDocumentProjectId = (int) $project->id;
        $this->deleteDocumentProjectName = $project->name;
        $this->deleteDocumentProjectCode = $project->pda_code;
        $this->deleteDocumentName = $project->file_name ?: basename($project->upload_pda);
    }

    public function closeDeleteDocumentModal(): void
    {
        $this->reset([
            'deleteDocumentProjectId', 'deleteDocumentProjectName',
            'deleteDocumentProjectCode', 'deleteDocumentName',
        ]);
        $this->dispatch('close-modal', 'delete-project-document');
    }

    public function deleteDocument(): void
    {
        abort_unless($this->deleteDocumentProjectId, 404);
        $project = $this->authorizedMutableProject($this->deleteDocumentProjectId);
        $path = $project->upload_pda;
        if (blank($path)) {
            $this->closeDeleteDocumentModal();
            return;
        }

        $project->update(['upload_pda' => null, 'file_name' => null]);
        if (str_starts_with($path, "projects/{$project->id}/documents/")) {
            Storage::disk('public')->delete($path);
        }
        $this->notifyProjectChange($project, 'Document deleted');
        $this->closeDeleteDocumentModal();
    }

    public function downloadDocument(): BinaryFileResponse
    {
        abort_unless($this->deleteDocumentProjectId, 404);
        $project = $this->authorizedMutableProject($this->deleteDocumentProjectId);
        abort_if(blank($project->upload_pda), 404);
        abort_unless(Storage::disk('public')->exists($project->upload_pda), 404);
        return response()->download(
            Storage::disk('public')->path($project->upload_pda),
            $project->file_name ?: basename($project->upload_pda)
        );
    }

    private function authorizedMutableProject(int $projectId): Project
    {
        $user = auth()->user();
        abort_unless($user, 403);
        return Project::query()->whereIn('company_id', $user
            ->companiesForPermissionQuery(ProjectPermissionEnum::Update)
            ->select('companies.id')->reorder())->findOrFail($projectId);
    }

    private function notifyProjectChange(Project $project, string $title): void
    {
        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: $title, position: 'center', timer: 1800);
    }
}
