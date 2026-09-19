<?php

namespace App\Livewire\Tools;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\UserPreference;
use App\Services\Tools\SapDataImporter;
use Livewire\Attributes\Locked;

trait ManagesSapImport
{
    public string $importPlant = '';

    public array $sapMapping = [];

    public array $selectedProjects = [];

    #[Locked]
    public array $matchedProjects = [];

    #[Locked]
    public bool $mappingConfirmed = false;

    #[Locked]
    public bool $hasSavedMapping = false;

    public bool $editingMapping = true;

    public bool $matchesReady = false;

    public string $importMessage = '';

    #[Locked]
    public array $projectReport = [];

    #[Locked]
    public array $sapPreviewRows = [];

    #[Locked]
    public array $sapPreviewProject = [];

    public function previewSapProject(int $projectId, SapDataImporter $importer): void
    {
        $this->validateSapFilters();
        abort_unless($this->mappingConfirmed, 409);
        $project = Project::where('company_id', $this->importPlant)->findOrFail($projectId);
        $rows = $importer->rows(auth()->user(), $project, $this->sourceToken, $this->sapMapping);
        $this->sapPreviewRows = array_slice($rows, 0, 25);
        $this->sapPreviewProject = ['order' => $project->sap_order, 'count' => count($rows)];
    }

    public function closeSapPreview(): void
    {
        $this->reset('sapPreviewRows', 'sapPreviewProject');
    }

    public function updatedImportPlant(): void
    {
        $this->clearSapMatches();
        if ($this->sourceToken !== '' && $this->mappingConfirmed) {
            $this->findSapMatches(app(SapDataImporter::class));
        }
    }

    public function updatedSapMapping(): void
    {
        $this->mappingConfirmed = false;
        $this->clearSapMatches();
    }

    public function editSapMapping(): void
    {
        $this->editingMapping = true;
        $this->mappingConfirmed = false;
        $this->clearSapMatches();
    }

    public function confirmSapMapping(SapDataImporter $importer): void
    {
        $this->resetValidation();
        $this->sapMapping = $importer->mapping(auth()->user(), $this->sourceToken, $this->sapMapping);
        UserPreference::updateOrCreate(
            ['user_id' => auth()->id(), 'key' => 'tools.sap-import.mapping.v2'],
            ['value' => $this->sapMapping]
        );
        $this->hasSavedMapping = true;
        $this->mappingConfirmed = true;
        $this->editingMapping = false;
        $this->clearSapMatches();
        if ($this->importPlant !== '') {
            $this->findSapMatches($importer);
        }
    }

    public function findSapMatches(SapDataImporter $importer): void
    {
        $this->clearSapMatches();
        $this->validateSapFilters();
        if (! $this->mappingConfirmed) {
            $this->addError('sapMapping', __('sap.error_confirm'));

            return;
        }
        $this->projectReport = $importer->matchReport(auth()->user(), (int) $this->importPlant, $this->sourceToken, $this->sapMapping);
        $this->matchedProjects = array_values(array_filter($this->projectReport, fn ($project) => $project['count'] > 0));
        $this->matchesReady = true;
    }

    public function importSap(SapDataImporter $importer): void
    {
        $this->validateSapFilters();
        abort_unless($this->mappingConfirmed, 409);
        $this->validate(['selectedProjects' => 'required|array|min:1', 'selectedProjects.*' => 'required|integer|distinct'], ['selectedProjects.required' => __('sap.error_select'), 'selectedProjects.min' => __('sap.error_select')]);
        $count = $importer->importSelected(auth()->user(), (int) $this->importPlant, $this->sourceToken, $this->sapMapping, $this->selectedProjects);
        $projects = count($this->selectedProjects);
        $this->importMessage = __('sap.success', ['count' => $count, 'projects' => $projects]);
        $this->selectedProjects = [];
        $this->resetValidation();
    }

    private function validateSapFilters(): void
    {
        $this->validate(['importPlant' => 'required|integer|exists:companies,id'], [
            'importPlant.*' => __('sap.error_plant'),
        ]);
        abort_unless(auth()->user()->hasPermissionInCompany(ProjectPermissionEnum::Update, (int) $this->importPlant), 403);
    }

    private function prepareSapMapping(): void
    {
        $this->clearSapMatches();
        $saved = auth()->user()->preferences()->where('key', 'tools.sap-import.mapping.v2')->value('value');
        $this->hasSavedMapping = is_array($saved) && $saved !== [];
        $this->mappingConfirmed = false;
        $this->editingMapping = ! $this->hasSavedMapping;
        $this->sapMapping = [];
        $suggested = app(SapDataImporter::class)->suggestMapping($this->headers);
        foreach (SapDataImporter::COLUMNS as $header => $field) {
            $this->sapMapping[$field] = $this->hasSavedMapping ? ($saved[$field] ?? '') : $suggested[$field];
        }
    }

    private function clearSapMatches(): void
    {
        $this->reset('matchedProjects', 'selectedProjects', 'matchesReady', 'importMessage', 'projectReport', 'sapPreviewRows', 'sapPreviewProject');
    }
}
