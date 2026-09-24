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
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Livewire\Project\Concerns\ManagesProjectIdeaUpload;
use App\Livewire\Project\Concerns\ManagesProjectHandoverCertificate;
use App\Livewire\Project\Concerns\ManagesProjectOwners;
use App\Models\Owner;

class Create extends Component
{
    use ManagesProjectIdeaUpload;
    use ManagesProjectHandoverCertificate;
    use ManagesProjectOwners;
    use WithFileUploads;

    public ProjectForm $form;
    public mixed $projectIdea = null;
    public mixed $pdaDocument = null;
    public mixed $handoverCertificate = null;

    public function updatedFormCompanyId(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $this->resetValidation([
            'form.company_id',
            'form.pda_code',
        ]);

        $this->form->updateCompanyCode($user);
        $this->form->owner_ids = [];
    }

    public function updatedFormState(): void
    {
        $this->form->handleStateChange();
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
        $this->resetOwnerCreator();
        $this->reset(['projectIdea', 'pdaDocument', 'handoverCertificate']);
        $this->resetValidation(['projectIdea', 'pdaDocument', 'handoverCertificate']);
        $this->dispatch('close-modal', 'create-project');
    }

    public function createProject(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);
        $this->validateProjectIdea();
        $this->validatePdaDocument();
        $this->validateHandoverCertificate();

        $project = $this->form->store($user);
        $this->storeProjectIdea($project);
        $this->storePdaDocument($project);
        $this->storeHandoverCertificate($project);

        $this->dispatch(
            'project-created',
            projectId: $project->getKey(),
        );

        $this->dispatch('close-modal', 'create-project');
        $this->form->resetForm();
        $this->resetOwnerCreator();
        $this->reset(['projectIdea', 'pdaDocument', 'handoverCertificate']);
    }

    private function validatePdaDocument(): void
    {
        if (! $this->pdaDocument) {
            return;
        }

        $this->validate([
            'pdaDocument' => ['required', 'file', 'extensions:pdf', 'mimes:pdf', 'max:10240'],
        ], [
            'pdaDocument.extensions' => __('The PDA must be a PDF file.'),
            'pdaDocument.mimes' => __('Only a valid PDF file is allowed.'),
            'pdaDocument.max' => __('The PDA may not be larger than 10 MB.'),
        ]);
    }

    private function storePdaDocument(Project $project): void
    {
        if (! $this->pdaDocument) {
            return;
        }

        $originalName = $this->pdaDocument->getClientOriginalName();
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'pda';
        $path = $this->pdaDocument->storeAs(
            "projects/{$project->id}/documents",
            Str::uuid().'-'.$baseName.'.pdf',
            'public'
        );

        $project->update(['upload_pda' => $path, 'file_name' => $originalName]);
    }

    public function render(): View
    {
        $companies = auth()->user()?->companiesForPermission(
            ProjectPermissionEnum::Create
        ) ?? collect();

        return view('livewire.project.create', [
            'companies' => $companies,
            'canCreate' => auth()->user()?->companiesForPermissionQuery(
                ProjectPermissionEnum::Create
            )->exists() ?? false,
            'stateOptions' => ProjectStateEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'rateLimits' => ProjectRateSetting::current(),
            'owners' => Owner::query()
                ->with('companies:id')
                ->whereHas('companies', fn ($query) => $query->whereKey($companies->pluck('id')))
                ->orderBy('name')
                ->get(),
        ]);
    }

    /** @return array<int, int> */
    protected function ownerCompanyIds(): array
    {
        return auth()->user()?->companiesForPermissionQuery(ProjectPermissionEnum::Create)
            ->pluck('companies.id')
            ->map(fn ($id): int => (int) $id)
            ->all() ?? [];
    }
}
