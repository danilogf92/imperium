<?php

namespace App\Livewire\Project;

use App\Livewire\Project\Concerns\ManagesProjectIdeas;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectIdeaManager extends Component
{
    use ManagesProjectIdeas;
    use WithFileUploads;

    public bool $isOpen = false;
    public mixed $projectIdeaFile = null;
    public ?int $projectIdeaProjectId = null;
    public string $projectIdeaProjectCode = '';
    public string $projectIdeaProjectName = '';
    public ?string $currentProjectIdeaFileName = null;
    public bool $projectIdeaCanManage = false;
    public bool $projectIdeaDeleteConfirmation = false;

    #[On('open-project-idea-manager')]
    public function openFromTable(int $projectId): void
    {
        $this->openProjectIdeaModal($projectId);
        $this->isOpen = true;
    }

    public function closeProjectIdeaModal(): void
    {
        $this->isOpen = false;
        $this->reset([
            'projectIdeaFile', 'projectIdeaProjectId', 'projectIdeaProjectCode',
            'projectIdeaProjectName', 'currentProjectIdeaFileName',
            'projectIdeaCanManage', 'projectIdeaDeleteConfirmation',
        ]);
        $this->resetValidation();
    }

    private function notifyProjectChange(Project $project, string $title): void
    {
        $this->dispatch('project-updated', projectId: $project->id);
        $this->dispatch('alert', type: 'success', title: $title, position: 'center', timer: 1800);
    }

    public function render(): View
    {
        return view('livewire.project.project-idea-manager');
    }
}
