<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use App\Models\ProjectRateSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Livewire\Project\Concerns\ManagesProjectIdeaUpload;

class Edit extends Component
{
    use ManagesProjectIdeaUpload;
    use WithFileUploads;

    public ProjectForm $form;
    public mixed $projectIdea = null;
    public ?string $currentProjectIdeaName = null;

    public int $projectId;

    public function mount(Project $project): void
    {
        $this->projectId = (int) $project->getKey();
        $this->form->setProject($project);
        $this->currentProjectIdeaName = $project->project_idea_name;
    }

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

    public function updatedFormState(): void
    {
        $this->form->handleStateChange();
    }

    public function openModal(): void
    {
        $this->form->setProject($this->authorizedProject());
        $this->reset('projectIdea');
        $this->currentProjectIdeaName = $this->authorizedProject()->project_idea_name;
        $this->dispatch('open-modal', $this->modalName());
    }

    #[On('open-project-edit')]
    public function openFromTableRow(int $projectId): void
    {
        if ($projectId === $this->projectId) {
            $this->openModal();
        }
    }

    public function closeModal(): void
    {
        $this->resetValidation();
        $this->reset('projectIdea');
        $this->dispatch('close-modal', $this->modalName());
    }

    public function updateProject(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);
        $this->validateProjectIdea();

        $currentProject = $this->authorizedProject();
        $newRate = (float) $this->form->rate;

        if ($newRate <= 0 && $currentProject->data()->exists()) {
            $this->addError(
                'form.rate',
                'The rate must be greater than zero because this project contains financial data.'
            );

            return;
        }

        $rateChanged = abs((float) $currentProject->rate - $newRate) > 0.0000001;

        $project = DB::transaction(function () use ($user, $rateChanged, $newRate): Project {
            $project = $this->form->update($user);

            if ($rateChanged && $newRate > 0) {
                $now = now();

                $project->data()->update([
                    'global_price_euros' => DB::raw("ROUND(global_price / {$newRate}, 2)"),
                    'real_value_euros' => DB::raw("ROUND(real_value / {$newRate}, 2)"),
                    'executed_euros' => DB::raw("ROUND(executed_dollars / {$newRate}, 2)"),
                    'booked_euros' => DB::raw("ROUND(booked / {$newRate}, 2)"),
                    'real_value_changed_at' => $now,
                    'executed_changed_at' => $now,
                    'booked_changed_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $project;
        });
        $this->storeProjectIdea($project);
        $this->currentProjectIdeaName = $project->fresh()->project_idea_name;

        $this->dispatch(
            'project-updated',
            projectId: $project->getKey(),
        );
        $this->dispatch('close-modal', $this->modalName());
    }

    public function render(): View
    {
        return view('livewire.project.create', [
            'isEdit' => true,
            'modalName' => $this->modalName(),
            'companies' => auth()->user()?->companiesForPermission(
                ProjectPermissionEnum::Update
            ) ?? collect(),
            'stateOptions' => ProjectStateEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'rateLimits' => ProjectRateSetting::current(),
        ]);
    }

    private function authorizedProject(): Project
    {
        $user = auth()->user();

        abort_unless($user, 403);

        return Project::query()
            ->with('company:id,company_code')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::Update
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($this->projectId);
    }

    private function modalName(): string
    {
        return 'edit-project-' . $this->projectId;
    }
}
