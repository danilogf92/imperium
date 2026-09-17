<?php

namespace Tests\Feature;

use App\Livewire\Planification\Planification;
use App\Exports\PlanificationExport;
use App\Models\Company;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PlanificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_percentage_calculates_and_displays_the_budget_value(): void
    {
        [$user, $project] = $this->projectContext();
        $milestone = Milestone::query()->where('code', 'WBS')->sole();

        Livewire::actingAs($user)
            ->test(Planification::class)
            ->set('projectId', $project->id)
            ->set('milestoneId', $milestone->id)
            ->set('month', 1)
            ->set('cycleYear', 2026)
            ->set('percentage', '25')
            ->call('saveMilestone')
            ->assertHasNoErrors()
            ->assertSee('WBS | $2,500.00');

        $this->assertDatabaseHas('project_milestones', [
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'percentage' => 25,
        ]);
    }

    public function test_project_cannot_receive_items_after_closed_milestone(): void
    {
        [$user, $project] = $this->projectContext();
        $closed = Milestone::query()->where('code', 'CLOSED')->sole();
        $wbs = Milestone::query()->where('code', 'WBS')->sole();

        $component = Livewire::actingAs($user)->test(Planification::class);

        $component
            ->set('projectId', $project->id)
            ->set('milestoneId', $closed->id)
            ->set('month', 1)
            ->set('cycleYear', 2026)
            ->set('percentage', '10')
            ->call('saveMilestone')
            ->assertHasNoErrors();

        $component
            ->set('projectId', $project->id)
            ->set('milestoneId', $wbs->id)
            ->set('month', 2)
            ->set('cycleYear', 2026)
            ->set('percentage', '10')
            ->call('saveMilestone')
            ->assertHasErrors(['projectId']);
    }

    public function test_milestone_percentages_cannot_exceed_one_hundred(): void
    {
        [$user, $project] = $this->projectContext();
        $wbs = Milestone::query()->where('code', 'WBS')->sole();
        $purchaseOrder = Milestone::query()->where('code', 'PO')->sole();

        $component = Livewire::actingAs($user)->test(Planification::class);

        $component
            ->set('projectId', $project->id)
            ->set('milestoneId', $wbs->id)
            ->set('month', 1)
            ->set('cycleYear', 2026)
            ->set('percentage', '70')
            ->call('saveMilestone')
            ->assertHasNoErrors();

        $component
            ->set('projectId', $project->id)
            ->set('milestoneId', $purchaseOrder->id)
            ->set('month', 2)
            ->set('cycleYear', 2026)
            ->set('percentage', '31')
            ->call('saveMilestone')
            ->assertHasErrors(['percentage']);
    }

    public function test_year_filter_matches_projects_forecast_start_year_and_shows_name_and_pda_code(): void
    {
        [$user, $project] = $this->projectContext();

        $otherProject = Project::query()->create([
            'company_id' => $project->company_id,
            'created_by' => $user->id,
            'name' => 'Project from another forecast year',
            'pda_code' => 'PDA-OTHER-YEAR',
            'rate' => 1,
            'state' => 'Planning',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2025-01-01',
            'forecast_end_date' => '2026-12-31',
        ]);

        Livewire::actingAs($user)
            ->test(Planification::class)
            ->set('creationYearFilter', ['2026'])
            ->assertViewHas('plannedProjects', function ($projects) use ($project, $otherProject): bool {
                return $projects->getCollection()->contains('id', $project->id)
                    && ! $projects->getCollection()->contains('id', $otherProject->id);
            })
            ->assertSee($project->name)
            ->assertSee($project->pda_code);
    }

    public function test_months_start_at_first_visible_milestone_after_filtering(): void
    {
        [$user, $project] = $this->projectContext();
        $project->update(['name' => 'May timeline project']);
        $project->projectMilestones()->create([
            'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
            'cycle_year' => 2026,
            'month' => 5,
            'sequence' => 1,
            'percentage' => 25,
        ]);
        [, $emptyProject] = $this->projectContext();
        $emptyProject->update(['name' => 'Empty timeline project']);

        $component = Livewire::actingAs($user)->test(Planification::class)
            ->assertViewHas('timelineStartMonth', 1)
            ->set('search', 'May timeline project')
            ->assertViewHas('timelineStartMonth', 5)
            ->assertViewHas('timelineColumnCount', 20);

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
        $monthHeaders = (new \DOMXPath($document))->query('//table/thead/tr[2]/th');
        $this->assertSame('MAY', trim($monthHeaders->item(0)->textContent));
        $this->assertSame(20, $monthHeaders->length);

        $component->set('search', 'Empty timeline project')
            ->assertViewHas('timelineStartMonth', 1)
            ->assertViewHas('timelineColumnCount', 24);
    }

    public function test_milestone_completion_filter_distinguishes_100_percent_from_lower_allocations(): void
    {
        [$user, $complete] = $this->projectContext();
        [, $partial] = $this->projectContext();
        [, $empty] = $this->projectContext();
        $complete->projectMilestones()->create([
            'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
            'cycle_year' => 2026,
            'month' => 5,
            'sequence' => 1,
            'percentage' => 60,
        ]);
        $complete->projectMilestones()->create([
            'milestone_id' => Milestone::where('code', 'PO')->value('id'),
            'cycle_year' => 2026,
            'month' => 6,
            'sequence' => 2,
            'percentage' => 40,
        ]);
        $partial->projectMilestones()->create([
            'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
            'cycle_year' => 2026,
            'month' => 6,
            'sequence' => 1,
            'percentage' => 40,
        ]);

        $component = Livewire::actingAs($user)->test(Planification::class)
            ->assertSee('With milestones')
            ->assertSee('Complete milestones (100%)')
            ->set('milestoneCompletionFilter', 'completed')
            ->assertViewHas('plannedProjects', fn ($projects): bool =>
                $projects->getCollection()->pluck('id')->all() === [$complete->id]);

        $component->set('milestoneCompletionFilter', 'incomplete')
            ->assertViewHas('plannedProjects', fn ($projects): bool =>
                ! $projects->getCollection()->contains('id', $complete->id)
                && $projects->getCollection()->contains('id', $partial->id)
                && $projects->getCollection()->contains('id', $empty->id))
            ->call('toggleOnlyWithMilestones')
            ->assertViewHas('plannedProjects', fn ($projects): bool =>
                $projects->getCollection()->contains('id', $partial->id)
                && ! $projects->getCollection()->contains('id', $empty->id))
            ->call('clearFilters')
            ->assertSet('milestoneCompletionFilter', '')
            ->assertSet('onlyWithMilestones', false)
            ->assertViewHas('plannedProjects', fn ($projects): bool =>
                $projects->getCollection()->contains('id', $complete->id)
                && $projects->getCollection()->contains('id', $partial->id)
                && $projects->getCollection()->contains('id', $empty->id));

        $filters = [
            'search' => '',
            'plants' => [],
            'statuses' => [],
            'creationYears' => [],
            'activityWeeks' => '',
            'milestoneCompletion' => '',
            'activityExecution' => '',
            'currency' => 'usd',
            'cellDisplay' => 'combined',
            'onlyWithMilestones' => false,
        ];
        foreach (['completed' => [$complete], 'incomplete' => [$partial, $empty]] as $status => $expected) {
            $response = (new PlanificationExport)->download($user, [
                ...$filters,
                'milestoneCompletion' => $status,
            ]);
            $path = $response->getFile()->getPathname();
            try {
                $workbook = IOFactory::load($path);
                $sheet = $workbook->getActiveSheet();
                $codes = [];
                for ($row = 3; $row <= $sheet->getHighestDataRow(); $row++) {
                    $codes[] = $sheet->getCell("C{$row}")->getValue();
                }
                $this->assertEqualsCanonicalizing(array_map(fn (Project $project) => $project->pda_code, $expected), $codes);
                $workbook->disconnectWorksheets();
            } finally {
                unlink($path);
            }
        }
    }

    public function test_postponed_projects_are_never_available_in_planification(): void
    {
        [$user, $project] = $this->projectContext();
        $project->update(['state' => 'Postponed']);

        Livewire::actingAs($user)
            ->test(Planification::class)
            ->assertViewHas('plannedProjects', fn ($projects): bool => ! $projects
                ->getCollection()
                ->contains('id', $project->id))
            ->assertViewHas('projects', fn ($projects): bool => ! $projects->contains('id', $project->id))
            ->assertViewHas('statusOptions', fn (array $states): bool => ! in_array('Postponed', $states, true))
            ->assertDontSee($project->name);
    }

    public function test_project_allocation_indicator_and_dashboard_link_follow_total_milestone_percentages(): void
    {
        [$user, $project] = $this->projectContext();
        $component = Livewire::actingAs($user)->test(Planification::class)
            ->assertSee('href="'.route('projects.dashboard', $project->slug).'"', false)
            ->assertSee('Allocated budget: 0%')
            ->assertSee('bg-orange-100 text-orange-700 ring-orange-200', false);

        foreach ([['PO', 50, 1, 50], ['WBS', 20, 2, 70], ['WBS', 29.99, 3, 99.99], ['WBS', 0.01, 4, 100]] as [$code, $percentage, $month, $total]) {
            $component->set('projectId', $project->id)
                ->set('milestoneId', Milestone::where('code', $code)->value('id'))
                ->set('month', $month)
                ->set('cycleYear', 2026)
                ->set('percentage', (string) $percentage)
                ->call('saveMilestone')
                ->assertHasNoErrors()
                ->assertSee('Allocated budget: '.$total.'%')
                ->assertSee($total < 100
                    ? 'bg-orange-100 text-orange-700 ring-orange-200'
                    : 'bg-emerald-100 text-emerald-700 ring-emerald-200', false);
        }

        $item = $project->projectMilestones()->first();
        $component->set('milestoneCompletionFilter', 'completed')
            ->assertSee('Allocated budget: 100%')
            ->assertViewHas('plannedProjects', fn ($projects) =>
                (float) $projects->first()->allocated_percentage === 100.0
                && $projects->first()->projectMilestones->count() === 4);

        $item->update(['percentage' => 40]);
        $component->set('milestoneCompletionFilter', 'incomplete')
            ->assertSee('Allocated budget: 90%')
            ->assertSee('bg-orange-100 text-orange-700 ring-orange-200', false);
    }

    /** @return array{User, Project} */
    private function projectContext(): array
    {
        $user = User::query()->where('email', 'test@example.com')->sole();
        $company = Company::query()->where('company_code', 'CIESA')->sole();
        $project = Project::query()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'name' => 'Planification test project',
            'pda_code' => 'PLAN-TEST-' . uniqid(),
            'rate' => 1,
            'state' => 'Planning',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2026-01-01',
            'forecast_end_date' => '2027-12-31',
        ]);

        return [$user, $project];
    }
}
