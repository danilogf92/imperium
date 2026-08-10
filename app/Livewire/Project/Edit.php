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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Livewire\Project\Concerns\ManagesProjectIdeaUpload;
use App\Livewire\Project\Concerns\ManagesProjectHandoverCertificate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Edit extends Component
{
    use ManagesProjectIdeaUpload;
    use ManagesProjectHandoverCertificate;
    use WithFileUploads;

    public ProjectForm $form;
    public mixed $projectIdea = null;
    public mixed $pdaDocument = null;
    public mixed $handoverCertificate = null;
    public ?string $currentProjectIdeaName = null;
    public ?string $currentPdaName = null;
    public ?string $currentHandoverCertificateName = null;

    public int $projectId;

    public function mount(Project $project): void
    {
        $this->projectId = (int) $project->getKey();
        $this->form->setProject($project);
        $this->currentProjectIdeaName = $project->project_idea_name;
        $this->currentPdaName = $project->file_name;
        $this->currentHandoverCertificateName = $project->handover_certificate_name;
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
        $this->reset(['projectIdea', 'pdaDocument', 'handoverCertificate']);
        $project = $this->authorizedProject();
        $this->currentProjectIdeaName = $project->project_idea_name;
        $this->currentPdaName = $project->file_name;
        $this->currentHandoverCertificateName = $project->handover_certificate_name;
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
        $this->reset(['projectIdea', 'pdaDocument', 'handoverCertificate']);
        $this->dispatch('close-modal', $this->modalName());
    }

    public function updateProject(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);
        $this->validateProjectIdea();
        $this->validatePdaDocument();
        $this->validateHandoverCertificate();

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
        $this->storePdaDocument($project);
        $this->storeHandoverCertificate($project);
        $freshProject = $project->fresh();
        $this->currentProjectIdeaName = $freshProject->project_idea_name;
        $this->currentPdaName = $freshProject->file_name;
        $this->currentHandoverCertificateName = $freshProject->handover_certificate_name;

        $this->dispatch(
            'project-updated',
            projectId: $project->getKey(),
        );
        $this->dispatch('close-modal', $this->modalName());
    }

    public function downloadCurrentProjectIdea(): BinaryFileResponse
    {
        $project = $this->authorizedProject();
        abort_if(blank($project->project_idea_path), 404);
        abort_unless(Storage::disk('public')->exists($project->project_idea_path), 404);

        return response()->download(
            Storage::disk('public')->path($project->project_idea_path),
            $project->project_idea_name ?: basename($project->project_idea_path)
        );
    }

    public function downloadCurrentPda(): BinaryFileResponse
    {
        $project = $this->authorizedProject();
        abort_if(blank($project->upload_pda), 404);
        abort_unless(Storage::disk('public')->exists($project->upload_pda), 404);

        return response()->download(
            Storage::disk('public')->path($project->upload_pda),
            $project->file_name ?: basename($project->upload_pda)
        );
    }

    public function downloadCurrentHandoverCertificate(): BinaryFileResponse
    {
        $project = $this->authorizedProject();
        abort_if(blank($project->handover_certificate_path), 404);
        abort_unless(Storage::disk('public')->exists($project->handover_certificate_path), 404);

        return response()->download(
            Storage::disk('public')->path($project->handover_certificate_path),
            $project->handover_certificate_name ?: basename($project->handover_certificate_path)
        );
    }

    private function validatePdaDocument(): void
    {
        if (! $this->pdaDocument) {
            return;
        }

        $this->validate([
            'pdaDocument' => ['required', 'file', 'extensions:pdf', 'mimes:pdf', 'max:10240'],
        ], [
            'pdaDocument.extensions' => 'The PDA must be a PDF file.',
            'pdaDocument.mimes' => 'Only a valid PDF file is allowed.',
            'pdaDocument.max' => 'The PDA may not be larger than 10 MB.',
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
        $previousPath = $project->upload_pda;
        $project->update(['upload_pda' => $path, 'file_name' => $originalName]);

        if (filled($previousPath) && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/documents/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->reset('pdaDocument');
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
