<?php

namespace App\Livewire\Data;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexData extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->project = Project::query()
            ->with('company:id,company_name,company_code')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($project->getKey());
    }

    public function render(): View
    {
        return view('livewire.data.index-data')
            ->layout('layouts.app');
    }
}
