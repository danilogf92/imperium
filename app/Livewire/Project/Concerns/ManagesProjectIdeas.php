<?php

namespace App\Livewire\Project\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ManagesProjectIdeas
{
    public function openProjectIdeaModal(int $projectId): void
    {
        $project = $this->authorizedProjectIdea($projectId, ProjectPermissionEnum::View);
        $this->projectIdeaProjectId = (int) $project->id;
        $this->projectIdeaProjectCode = $project->pda_code;
        $this->projectIdeaProjectName = $project->name;
        $this->currentProjectIdeaFileName = $project->project_idea_name;
        $this->projectIdeaCanManage = auth()->user()->companiesForPermissionQuery(ProjectPermissionEnum::Update)
            ->whereKey($project->company_id)->exists();
        $this->reset('projectIdeaFile');
        $this->resetValidation('projectIdeaFile');
        $this->dispatch('open-modal', 'manage-project-ideas');
    }

    public function closeProjectIdeaModal(): void
    {
        $this->reset([
            'projectIdeaFile', 'projectIdeaProjectId', 'projectIdeaProjectCode',
            'projectIdeaProjectName', 'currentProjectIdeaFileName',
            'projectIdeaCanManage',
        ]);
        $this->resetValidation('projectIdeaFile');
        $this->dispatch('close-modal', 'manage-project-ideas');
    }

    public function saveProjectIdea(): void
    {
        abort_unless($this->projectIdeaProjectId, 404);
        $project = $this->authorizedProjectIdea($this->projectIdeaProjectId, ProjectPermissionEnum::Update);
        $this->validate([
            'projectIdeaFile' => ['required', 'file', 'extensions:xlsx,xls', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'projectIdeaFile.required' => 'Select an Excel file.',
            'projectIdeaFile.extensions' => 'Only Excel files (.xlsx or .xls) are allowed.',
            'projectIdeaFile.mimes' => 'Only valid Excel files are allowed.',
            'projectIdeaFile.max' => 'The Excel file may not be larger than 10 MB.',
        ]);

        $originalName = $this->projectIdeaFile->getClientOriginalName();
        $extension = strtolower($this->projectIdeaFile->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'project-ideas';
        $fileName = now()->format('YmdHis').'-'.$baseName.'.'.$extension;
        $path = $this->projectIdeaFile->storeAs("projects/{$project->id}/ideas", $fileName, 'public');
        $previousPath = $project->project_idea_path;
        $project->update(['project_idea_path' => $path, 'project_idea_name' => $originalName]);

        if (filled($previousPath) && str_starts_with($previousPath, "projects/{$project->id}/ideas/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->notifyProjectChange($project, 'Project ideas updated');
        $this->closeProjectIdeaModal();
    }

    public function downloadProjectIdea(): BinaryFileResponse
    {
        abort_unless($this->projectIdeaProjectId, 404);
        $project = $this->authorizedProjectIdea($this->projectIdeaProjectId, ProjectPermissionEnum::View);
        abort_if(blank($project->project_idea_path), 404);
        abort_unless(Storage::disk('public')->exists($project->project_idea_path), 404);

        return response()->download(Storage::disk('public')->path($project->project_idea_path),
            $project->project_idea_name ?: basename($project->project_idea_path));
    }

    public function deleteProjectIdea(): void
    {
        abort_unless($this->projectIdeaProjectId, 404);
        $project = $this->authorizedProjectIdea($this->projectIdeaProjectId, ProjectPermissionEnum::Update);
        $path = $project->project_idea_path;
        abort_if(blank($path), 404);
        $project->update(['project_idea_path' => null, 'project_idea_name' => null]);

        if (str_starts_with($path, "projects/{$project->id}/ideas/")) {
            Storage::disk('public')->delete($path);
        }

        $this->notifyProjectChange($project, 'Project ideas deleted');
        $this->closeProjectIdeaModal();
    }

    private function authorizedProjectIdea(int $projectId, ProjectPermissionEnum $permission): Project
    {
        $user = auth()->user();
        abort_unless($user, 403);

        return Project::query()->whereIn('company_id', $user->companiesForPermissionQuery($permission)
            ->select('companies.id')->reorder())->findOrFail($projectId);
    }
}
