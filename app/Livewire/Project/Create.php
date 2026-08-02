<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Livewire\Forms\ProjectForm;
use App\Models\ProjectRateSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    public ProjectForm $form;

    public function updatedFormCompanyId(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $this->resetValidation([
            'form.company_id',
            'form.pda_code',
        ]);

        $this->form->updateCompanyCode($user);
    }

    public function openCreateModal(): void
    {
        abort_unless(
            auth()->user()?->companiesForPermissionQuery(
                ProjectPermissionEnum::Create
            )->exists(),
            403
        );

        $this->resetValidation();
        $this->dispatch('open-modal', 'create-project');
    }

    public function closeCreateModal(): void
    {
        $this->form->resetForm();
        $this->dispatch('close-modal', 'create-project');
    }

    public function createProject(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $project = $this->form->store($user);

        $this->dispatch(
            'project-created',
            projectId: $project->getKey(),
        );

        $this->dispatch('close-modal', 'create-project');
        $this->form->resetForm();
    }

    public function render(): View
    {
        return view('livewire.project.create', [
            'companies' => auth()->user()?->companiesForPermission(
                ProjectPermissionEnum::Create
            ) ?? collect(),
            'canCreate' => auth()->user()?->companiesForPermissionQuery(
                ProjectPermissionEnum::Create
            )->exists() ?? false,
            'stateOptions' => ProjectStateEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'rateLimits' => ProjectRateSetting::current(),
        ]);
    }
}
