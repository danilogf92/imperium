<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectHandoverCertificateManager extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public bool $deleteConfirmation = false;
    public mixed $document = null;
    public ?int $projectId = null;
    public string $projectName = '';
    public string $projectCode = '';
    public ?string $currentDocumentName = null;

    #[On('open-project-handover-certificate-manager')]
    public function open(int $projectId): void
    {
        $project = $this->authorizedProject($projectId);
        $this->projectId = (int) $project->id;
        $this->projectName = $project->name;
        $this->projectCode = $project->pda_code;
        $this->currentDocumentName = $project->handover_certificate_name
            ?: (filled($project->handover_certificate_path) ? basename($project->handover_certificate_path) : null);
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

    public function saveDocument(): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);

        $this->validate([
            'document' => ['required', 'file', 'extensions:pdf', 'mimes:pdf', 'max:10240'],
        ], [
            'document.required' => 'Select a Project Handover Certificate PDF.',
            'document.extensions' => 'The Project Handover Certificate must be a PDF file.',
            'document.mimes' => 'Only a valid PDF file is allowed.',
            'document.max' => 'The Project Handover Certificate may not be larger than 10 MB.',
        ]);

        $originalName = $this->document->getClientOriginalName();
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'project-handover-certificate';
        $path = $this->document->storeAs(
            "projects/{$project->id}/handover-certificates",
            Str::uuid().'-'.$baseName.'.pdf',
            'public'
        );
        $previousPath = $project->handover_certificate_path;

        $project->update([
            'handover_certificate_path' => $path,
            'handover_certificate_name' => $originalName,
        ]);

        if (filled($previousPath) && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/handover-certificates/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: 'Project Handover Certificate uploaded', position: 'center', timer: 1800);
        $this->close();
    }

    public function download(): BinaryFileResponse
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        abort_if(blank($project->handover_certificate_path), 404);
        abort_unless(Storage::disk('public')->exists($project->handover_certificate_path), 404);

        return response()->download(
            Storage::disk('public')->path($project->handover_certificate_path),
            $project->handover_certificate_name ?: basename($project->handover_certificate_path)
        );
    }

    public function delete(): void
    {
        abort_unless($this->projectId, 404);
        $project = $this->authorizedProject($this->projectId);
        $path = $project->handover_certificate_path;
        abort_if(blank($path), 404);

        $project->update([
            'handover_certificate_path' => null,
            'handover_certificate_name' => null,
        ]);

        if (str_starts_with($path, "projects/{$project->id}/handover-certificates/")) {
            Storage::disk('public')->delete($path);
        }

        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: 'Project Handover Certificate deleted', position: 'center', timer: 1800);
        $this->close();
    }

    private function authorizedProject(int $projectId): Project
    {
        $user = auth()->user();
        abort_unless($user, 403);

        return Project::query()
            ->whereIn('company_id', $user->companiesForPermissionQuery(ProjectPermissionEnum::Update)
                ->select('companies.id')->reorder())
            ->findOrFail($projectId);
    }

    public function render(): View
    {
        return view('livewire.project.project-handover-certificate-manager');
    }
}
