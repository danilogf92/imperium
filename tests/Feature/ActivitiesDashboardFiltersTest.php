<?php

namespace Tests\Feature;

use App\Enums\ProjectPermissionEnum;
use App\Livewire\Activities\ActivitiesDashboard;
use App\Livewire\Dashboard\Dashboard;
use App\Models\Company;
use App\Models\Data;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectWeeklyActivity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivitiesDashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->travelTo(now()->setDate(2026, 9, 5));
    }

    public function test_each_project_filter_updates_activities_milestones_metrics_and_charts(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $selected = $this->createProject($user);
        $this->createProject($user, [
            'company_id' => Company::where('company_code', 'GRALCO')->value('id'),
            'forecast_start_date' => '2025-01-01',
            'state' => 'Execution',
            'classification_of_investments' => 'Land',
            'investments' => 'Maintenance',
            'justification' => 'Special Project',
        ]);

        $component = Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->assertViewHas('metrics', fn ($metrics) => $metrics['total'] === 2);

        foreach ([
            'companyFilter' => ['CIESA'],
            'yearSearch' => ['2026'],
            'stateSearch' => ['Planning'],
            'typeOfProjectSearch' => ['Buildings'],
            'investmentSearch' => ['Innovation'],
            'justificationSearch' => ['Normal Capex'],
        ] as $property => $values) {
            $component->call('resetAll')->set($property, $values)
                ->assertViewHas('metrics', fn ($metrics) => $metrics['total'] === 1 && $metrics['overdue'] === 1)
                ->assertViewHas('milestoneMetrics', fn ($metrics) => $metrics['total'] === 1)
                ->assertViewHas('activities', fn ($items) => $items->pluck('project_id')->all() === [$selected->id])
                ->assertViewHas('topOverdueActivities', fn ($items) => $items->pluck('project_id')->all() === [$selected->id])
                ->assertViewHas('urgentMilestones', fn ($items) => $items->pluck('project_id')->all() === [$selected->id])
                ->assertViewHas('statusChart', fn ($chart) => array_sum($chart['series']) === 1)
                ->assertViewHas('milestoneStatusChart', fn ($chart) => array_sum($chart['series']) === 1);
        }

        $component->set('yearSearch', ['2025'])
            ->assertViewHas('metrics', fn ($metrics) => $metrics['total'] === 0)
            ->assertViewHas('milestoneMetrics', fn ($metrics) => $metrics['total'] === 0)
            ->call('resetAll')
            ->set('yearSearch', ['2025', '2026'])
            ->assertViewHas('metrics', fn ($metrics) => $metrics['total'] === 2)
            ->set('yearSearch', ['2026', '2026', 'invalid'])
            ->assertSet('yearSearch', ['2026']);
    }

    public function test_postponed_and_unauthorized_projects_stay_excluded(): void
    {
        $owner = User::where('email', 'test@example.com')->sole();
        $viewer = User::factory()->create();
        $viewer->assignRole('PROJECT MANAGER CIESA');
        $selected = $this->createProject($owner);
        $this->createProject($owner, ['state' => 'Postponed']);
        $this->createProject($owner, [
            'company_id' => Company::where('company_code', 'GRALCO')->value('id'),
            'forecast_start_date' => '2024-01-01',
        ]);

        Livewire::actingAs($viewer)->test(ActivitiesDashboard::class)
            ->assertSet('years', ['2026'])
            ->set('companyFilter', ['CIESA', 'GRALCO'])
            ->assertSet('companyFilter', ['CIESA'])
            ->set('stateSearch', ['Postponed'])
            ->assertSet('stateSearch', [])
            ->assertViewHas('metrics', fn ($metrics) => $metrics['total'] === 1)
            ->assertViewHas('milestoneMetrics', fn ($metrics) => $metrics['total'] === 1)
            ->assertViewHas('activities', fn ($items) => $items->pluck('project_id')->all() === [$selected->id]);
    }

    public function test_dashboard_keeps_its_filters_and_currency_after_sharing_filter_logic(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $this->createProject($user);
        $this->createProject($user, ['forecast_start_date' => '2025-01-01']);

        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('projectCount', 2)
            ->set('yearSearch', ['2026'])
            ->assertViewHas('projectCount', 1)
            ->set('currency', 'dollar')
            ->assertSet('currency', 'dollar')
            ->call('resetAll')
            ->assertSet('yearSearch', [])
            ->assertSet('currency', 'euro')
            ->assertViewHas('projectCount', 2);
    }

    public function test_dashboard_only_shows_plants_with_view_permission_and_refreshes_cached_totals(): void
    {
        $owner = User::where('email', 'test@example.com')->sole();
        $viewer = User::factory()->create(['is_active' => true]);
        $ciesaRole = Role::where('name', 'PROJECT MANAGER CIESA')->sole();
        $gralcoRole = Role::where('name', 'PROJECT MANAGER GRALCO')->sole();
        $viewer->assignRole([$ciesaRole, $gralcoRole]);
        $gralcoRole->revokePermissionTo(ProjectPermissionEnum::View->value);

        $ciesaProject = $this->createProject($owner);
        $gralcoProject = $this->createProject($owner, [
            'company_id' => Company::where('company_code', 'GRALCO')->value('id'),
            'forecast_start_date' => '2024-01-01',
        ]);
        Data::create(['project_id' => $ciesaProject->id, 'global_price_euros' => 100]);
        Data::create(['project_id' => $gralcoProject->id, 'global_price_euros' => 900]);

        Livewire::actingAs($viewer)->test(Dashboard::class)
            ->assertSet('years', ['2026'])
            ->assertViewHas('projectCount', 1)
            ->assertViewHas('budgeted', 100.0)
            ->assertViewHas('companies', fn ($companies) => $companies->pluck('company_code')->all() === ['CIESA'])
            ->set('companyFilter', ['CIESA', 'GRALCO'])
            ->assertSet('companyFilter', ['CIESA']);

        $gralcoRole->givePermissionTo(ProjectPermissionEnum::View->value);
        Livewire::actingAs($viewer)->test(Dashboard::class)
            ->assertViewHas('projectCount', 2)
            ->assertViewHas('budgeted', 1000.0)
            ->assertSet('years', ['2026', '2024']);

        $ciesaRole->revokePermissionTo(ProjectPermissionEnum::View->value);
        Livewire::actingAs($viewer)->test(Dashboard::class)
            ->assertViewHas('projectCount', 1)
            ->assertViewHas('budgeted', 900.0)
            ->assertViewHas('companies', fn ($companies) => $companies->pluck('company_code')->all() === ['GRALCO']);
    }

    private function createProject(User $user, array $attributes = []): Project
    {
        $project = Project::create(array_merge([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id,
            'name' => 'Activities filter project '.uniqid(),
            'pda_code' => 'FILTER-'.uniqid(),
            'rate' => 1,
            'state' => 'Planning',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2026-01-01',
            'forecast_end_date' => '2027-12-31',
        ], $attributes));

        // The item year deliberately differs from the project's forecast year.
        ProjectWeeklyActivity::create([
            'project_id' => $project->id,
            'week_year' => 2025,
            'week_number' => 10,
            'activity' => 'Overdue activity',
        ]);
        ProjectMilestone::create([
            'project_id' => $project->id,
            'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
            'cycle_year' => 2025,
            'month' => 3,
            'sequence' => 1,
            'percentage' => 10,
        ]);

        return $project;
    }
}
