<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Delete extends Component
{
    public int $projectId;

    public string $projectName;

    public function mount(Project $project): void
    {
        $this->projectId = (int) $project->getKey();
        $this->projectName = $project->name;
    }

    public function openModal(): void
    {
        $project = $this->authorizedProject();
        $this->projectName = $project->name;

        $this->dispatch('open-modal', $this->modalName());
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', $this->modalName());
    }

    public function deleteProject(): void
    {
        $project = $this->authorizedProject();
        $projectId = (int) $project->getKey();

        $project->delete();

        $this->dispatch('project-deleted', projectId: $projectId);
        $this->dispatch('close-modal', $this->modalName());
    }

    public function render(): View
    {
        return view('livewire.project.delete', [
            'modalName' => $this->modalName(),
        ]);
    }

    private function authorizedProject(): Project
    {
        $user = auth()->user();

        abort_unless($user, 403);

        return Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::Delete
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($this->projectId);
    }

    private function modalName(): string
    {
        return 'delete-project-'.$this->projectId;
    }
}
