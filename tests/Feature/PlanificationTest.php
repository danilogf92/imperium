<?php

namespace Tests\Feature;

use App\Livewire\Planification\Planification;
use App\Models\Company;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
