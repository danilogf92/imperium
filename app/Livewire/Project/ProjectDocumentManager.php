<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Validation\ProjectDocumentUploadValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectDocumentManager extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public bool $deleteConfirmation = false;
    public mixed $document = null;
    public ?int $projectId = null;
    public string $projectName = '';
    public string $projectCode = '';
    public ?string $currentDocumentName = null;

    #[On('open-project-document-manager')]
    public function open(int $projectId): void
    {
        $project = $this->authorizedProject($projectId);
        $this->projectId = (int) $project->id;
        $this->projectName = $project->name;
        $this->projectCode = $project->pda_code;
        $this->currentDocumentName = $project->file_name ?: (filled($project->upload_pda) ? basename($project->upload_pda) : null);
        $this->deleteConfirmation = false;
        $this->reset('document');
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['document', 'projectId', 'projectName', 'projectCode', 'currentDocumentName', 'deleteConfirmation']);
        $this->resetValidation();
    }

    public function uploadPdaDocument(): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        abort_if(filled($project->upload_pda), 409, 'Delete the current PDA before uploading another one.');
        $this->validate(ProjectDocumentUploadValidation::rules(), ProjectDocumentUploadValidation::messages(), ProjectDocumentUploadValidation::attributes());

        $originalName = $this->document->getClientOriginalName();
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'pda';
        $path = $this->document->storeAs("projects/{$project->id}/documents", Str::uuid().'-'.$baseName.'.pdf', 'public');
        $project->update(['upload_pda' => $path, 'file_name' => $originalName]);
        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: 'PDA uploaded', position: 'center', timer: 1800);
        $this->close();
    }

    public function download(): BinaryFileResponse
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        abort_if(blank($project->upload_pda), 404);
        abort_unless(Storage::disk('public')->exists($project->upload_pda), 404);
        return response()->download(Storage::disk('public')->path($project->upload_pda), $project->file_name ?: basename($project->upload_pda));
    }

    public function delete(): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        $path = $project->upload_pda;
        abort_if(blank($path), 404);
        $project->update(['upload_pda' => null, 'file_name' => null]);
        if (str_starts_with($path, "projects/{$project->id}/documents/")) {
            Storage::disk('public')->delete($path);
        }
        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: 'PDA deleted', position: 'center', timer: 1800);
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
        return view('livewire.project.project-document-manager');
    }
}
