<?php

namespace App\Livewire\Forms;

use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Validation\ProjectCreateValidation;
use App\Validation\ProjectUpdateValidation;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class ProjectForm extends Form
{
    public int|string|null $company_id = null;
    public ?int $project_id = null;
    public string $company_code = '';
    public string $name = '';
    public string $pda_code = '';
    public string $rate = '';
    public string $state = ProjectStateEnum::Planning->value;
    public string $investments = '';
    public string $justification = '';
    public string $classification_of_investments = '';
    public ?string $forecast_start_date = null;
    public ?string $forecast_end_date = null;
    public ?string $approve_date = null;
    public ?string $close_date = null;
    public ?string $quartile_date = null;
    public bool $data_uploaded = false;

    public function updateCompanyCode(User $user): void
    {
        $this->company_code = '';
        $this->pda_code = '';

        $company = $this->findAvailableCompany($user);

        if ($company) {
            $this->company_code = $this->normalizeCode($company->company_code);
        }
    }

    /**
     * @throws ValidationException
     */
    public function store(User $user): Project
    {
        $company = $this->findCompanyForPermission(
            $user,
            ProjectPermissionEnum::Create
        );

        if (! $company) {
            throw ValidationException::withMessages([
                'form.company_id' =>
                    'You cannot create projects in the selected company.',
            ]);
        }

        $validated = $this->validatedData($company);

        return Project::create([
            ...$validated,
            'created_by' => $user->getKey(),
            'responsible_id' => null,
        ]);
    }

    public function setProject(Project $project): void
    {
        $companyCode = $this->normalizeCode(
            $project->company?->company_code ?? ''
        );
        $pdaPrefix = $companyCode.'_';

        $this->project_id = (int) $project->getKey();
        $this->company_id = (int) $project->company_id;
        $this->company_code = $companyCode;
        $this->name = $project->name;
        $this->pda_code = str_starts_with($project->pda_code, $pdaPrefix)
            ? substr($project->pda_code, strlen($pdaPrefix))
            : $project->pda_code;
        $this->rate = (string) $project->rate;
        $this->state = $project->state->value;
        $this->investments = $project->investments->value;
        $this->justification = $project->justification->value;
        $this->classification_of_investments =
            $project->classification_of_investments->value;
        $this->forecast_start_date = $project->forecast_start_date?->format('Y-m-d');
        $this->forecast_end_date = $project->forecast_end_date?->format('Y-m-d');
        $this->approve_date = $project->approve_date?->format('Y-m-d');
        $this->close_date = $project->close_date?->format('Y-m-d');
        $this->quartile_date = $project->quartile_date?->format('Y-m-d');
        $this->data_uploaded = (bool) $project->data_uploaded;
        $this->resetValidation();
    }

    public function update(User $user): Project
    {
        $project = $this->findAvailableProject($user);
        $company = $this->findCompanyForPermission(
            $user,
            ProjectPermissionEnum::Update
        );

        if (! $company) {
            throw ValidationException::withMessages([
                'form.company_id' =>
                    'You cannot edit projects in the selected company.',
            ]);
        }

        $project->update($this->validatedData($company, $project));

        return $project;
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->state = ProjectStateEnum::Planning->value;
        $this->resetValidation();
    }

    private function findAvailableCompany(User $user): ?Company
    {
        if (! $this->company_id) {
            return null;
        }

        return $user->availableCompaniesQuery()
            ->select(['companies.id', 'companies.company_code'])
            ->find($this->company_id);
    }

    private function findCompanyForPermission(
        User $user,
        ProjectPermissionEnum $permission
    ): ?Company {
        if (! $this->company_id) {
            return null;
        }

        return $user->companiesForPermissionQuery($permission)
            ->select(['companies.id', 'companies.company_code'])
            ->find($this->company_id);
    }

    private function findAvailableProject(User $user): Project
    {
        return Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::Update
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($this->project_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(
        Company $company,
        ?Project $project = null
    ): array {
        $this->company_code = $this->normalizeCode($company->company_code);
        $editablePdaCode = $this->normalizeCode($this->pda_code);

        $this->name = trim($this->name);
        $this->pda_code = $this->company_code.'_'.$editablePdaCode;

        try {
            $validator = $project
                ? ProjectUpdateValidation::class
                : ProjectCreateValidation::class;

            $rules = $project
                ? ProjectUpdateValidation::rules($project)
                : ProjectCreateValidation::rules();

            return $this->validate(
                $rules,
                $validator::messages(),
                $validator::attributes(),
            );
        } finally {
            $this->pda_code = $editablePdaCode;
        }
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

}
