<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ManagesProjectIdeaUpload
{
    private function validateProjectIdea(): void
    {
        if (! $this->projectIdea) {
            return;
        }

        $this->validate([
            'projectIdea' => ['required', 'file', 'extensions:xlsx,xls', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'projectIdea.extensions' => 'Project ideas must be an Excel file (.xlsx or .xls).',
            'projectIdea.mimes' => 'Only valid Excel files are allowed.',
            'projectIdea.max' => 'Project ideas may not be larger than 10 MB.',
        ]);
    }

    private function storeProjectIdea(Project $project): void
    {
        if (! $this->projectIdea) {
            return;
        }

        $originalName = $this->projectIdea->getClientOriginalName();
        $extension = strtolower($this->projectIdea->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'project-ideas';
        $fileName = Str::uuid().'-'.$baseName.'.'.$extension;
        $path = $this->projectIdea->storeAs("projects/{$project->id}/ideas", $fileName, 'public');
        $previousPath = $project->project_idea_path;

        $project->update([
            'project_idea_path' => $path,
            'project_idea_name' => $originalName,
        ]);

        if (filled($previousPath) && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/ideas/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->reset('projectIdea');
    }
}
