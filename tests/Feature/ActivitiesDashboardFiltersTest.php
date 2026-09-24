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
use App\Models\UserPreference;
use Carbon\CarbonImmutable;
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
        $this->actingAs($user)->get(route('activities'))->assertOk();
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

    public function test_user_scope_cannot_be_bypassed_and_assignments_are_not_grouped_by_author(): void
    {
        $owner = User::where('email', 'test@example.com')->sole();
        $viewer = User::factory()->create();
        $viewer->assignRole('PROJECT MANAGER CIESA');
        $local = User::factory()->create(['name' => 'Local assignee']);
        $local->assignRole('PROJECT MANAGER CIESA');
        $foreign = User::factory()->create(['name' => 'Foreign assignee']);
        $foreign->assignRole('PROJECT MANAGER GRALCO');
        $project = $this->createProject($owner);
        $foreignProject = $this->createProject($owner, ['company_id' => Company::where('company_code', 'GRALCO')->value('id')]);
        $activity = $this->assignedActivity($project, $local, '2026-08-30', ['created_by' => $foreign->id]);
        $this->assignedActivity($foreignProject, $local, '2026-08-30');
        $this->assignedActivity($project, $foreign, '2026-08-30');

        $component = Livewire::actingAs($viewer)->test(ActivitiesDashboard::class)
            ->assertViewHas('users', fn ($users) => $users->contains('id', $local->id) && ! $users->contains('id', $foreign->id))
            ->set('userFilter', (string) $local->id)
            ->assertViewHas('activities', fn ($items) => $items->pluck('id')->all() === [$activity->id])
            ->assertViewHas('userSummary', fn ($rows) => $rows->count() === 1 && $rows->first()['overdue'] === 1)
            ->set('userFilter', (string) $foreign->id)
            ->assertViewHas('activities', fn ($items) => $items->total() === 0)
            ->assertViewHas('userSummary', fn ($rows) => $rows->isEmpty())
            ->set('userFilter', '')
            ->set('projectFilter', (string) $foreignProject->id)
            ->assertViewHas('activities', fn ($items) => $items->total() === 0);

        $component->call('showUserOverdue', $foreign->id)->assertForbidden();
    }

    public function test_weekly_deadlines_status_filters_and_user_summary_agree(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 23));
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $overdue = $this->assignedActivity($project, $user, '2026-09-20');
        $pending = $this->assignedActivity($project, $user, '2026-09-27');
        $future = $this->assignedActivity($project, $user, '2026-12-27');
        $completed = $this->assignedActivity($project, $user, '2026-09-13', ['executed_at' => now()]);
        ProjectWeeklyActivity::create(['project_id' => $project->id, 'assigned_to' => $user->id, 'activity' => 'General note']);

        $component = Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->set('userFilter', (string) $user->id)
            ->assertViewHas('metrics', fn ($m) => $m['total'] === 4 && $m['pending'] === 2 && $m['overdue'] === 1 && $m['completed'] === 1)
            ->assertViewHas('userSummary', fn ($rows) => $rows->first()['total'] === 4 && $rows->first()['overdue'] === 1)
            ->assertViewHas('topOverdueActivities', fn ($items) => $items->pluck('id')->all() === [$overdue->id]);

        foreach (['overdue' => [$overdue->id], 'pending' => [$pending->id, $future->id], 'completed' => [$completed->id]] as $status => $ids) {
            $component->set('status', $status)
                ->assertViewHas('activities', fn ($items) => $items->pluck('id')->all() === $ids)
                ->assertViewHas('userSummary', fn ($rows) => $rows->first()['total'] === count($ids));
        }

        $component->call('showUserOverdue', $user->id)
            ->assertSet('status', 'overdue')->assertSet('userFilter', (string) $user->id)
            ->assertDispatched('activities-filtered')
            ->assertViewHas('activities', fn ($items) => $items->pluck('id')->all() === [$overdue->id]);

        $this->travelTo(now()->setDate(2026, 9, 27)->endOfDay());
        Livewire::actingAs($user)->test(ActivitiesDashboard::class)->set('userFilter', (string) $user->id)
            ->assertViewHas('metrics', fn ($m) => $m['overdue'] === 1);
        $this->travelTo(now()->addDay()->startOfDay());
        Livewire::actingAs($user)->test(ActivitiesDashboard::class)->set('userFilter', (string) $user->id)
            ->assertViewHas('metrics', fn ($m) => $m['overdue'] === 2);
    }

    public function test_plant_project_user_status_and_due_dates_combine_and_reset(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $otherProject = $this->createProject($user);
        $selected = $this->assignedActivity($project, $user, '2026-08-30');
        $this->assignedActivity($project, $user, '2026-08-23');
        $this->assignedActivity($otherProject, $user, '2026-08-30');
        $foreign = User::factory()->create();
        $foreign->assignRole('PROJECT MANAGER GRALCO');

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->set('companyFilter', ['CIESA'])
            ->assertViewHas('users', fn ($users) => ! $users->contains('id', $foreign->id))
            ->set('userFilter', (string) $user->id)
            ->set('projectFilter', (string) $project->id)
            ->set('status', 'overdue')
            ->set('dateFrom', '2026-08-30')->set('dateTo', '2026-08-30')
            ->assertViewHas('activities', fn ($items) => $items->pluck('id')->all() === [$selected->id])
            ->assertViewHas('userSummary', fn ($rows) => $rows->first()['overdue'] === 1)
            ->set('dateTo', '2026-08-29')->assertHasErrors('dateTo')
            ->call('resetAll')->assertSet('userFilter', '')->assertSet('projectFilter', '')
            ->assertSet('dateFrom', '')->assertSet('dateTo', '')->assertSet('status', 'all')
            ->assertSet('companyFilter', []);
    }

    public function test_overdue_drilldown_paginates_all_matches_and_filters_reset_the_page(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        for ($i = 0; $i < 51; $i++) {
            $this->assignedActivity($project, $user, '2026-08-30');
        }

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->set('activityPerPage', 50)
            ->call('showUserOverdue', $user->id)
            ->assertViewHas('activities', fn ($items) => $items->total() === 51 && $items->count() === 50)
            ->assertViewHas('userSummary', fn ($rows) => $rows->first()['overdue'] === 51)
            ->call('setPage', 2)
            ->assertViewHas('activities', fn ($items) => $items->count() === 1)
            ->set('status', 'completed')->assertSet('paginators.page', 1);
    }

    public function test_user_options_and_assignments_follow_permission_revocation(): void
    {
        $owner = User::where('email', 'test@example.com')->sole();
        $viewer = User::factory()->create();
        $viewer->assignRole('PROJECT MANAGER CIESA');
        $assignee = User::factory()->create(['is_active' => false]);
        $assignee->assignRole('PROJECT MANAGER CIESA');
        $project = $this->createProject($owner);
        $this->assignedActivity($project, $assignee, '2026-08-30');

        $component = Livewire::actingAs($viewer)->test(ActivitiesDashboard::class)
            ->assertViewHas('users', fn ($users) => $users->contains('id', $assignee->id))
            ->set('userFilter', (string) $assignee->id)
            ->assertViewHas('metrics', fn ($m) => $m['overdue'] === 1);

        $viewer->removeRole('PROJECT MANAGER CIESA');
        $component->call('$refresh')
            ->assertViewHas('users', fn ($users) => $users->isEmpty())
            ->assertViewHas('userSummary', fn ($rows) => $rows->isEmpty())
            ->assertViewHas('activities', fn ($items) => $items->total() === 0);
    }

    public function test_ui_preferences_persist_independently_by_user_without_changing_other_tables(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $other = User::factory()->create();
        $other->assignRole('PROJECT MANAGER CIESA');
        UserPreference::create(['user_id' => $user->id, 'key' => 'tables.per_page', 'value' => ['count' => 50]]);

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->assertSet('activityPerPage', 10)->assertSet('milestonePerPage', 10)->assertSet('summaryOpen', true)
            ->set('activityPerPage', 20)->set('milestonePerPage', 5)
            ->call('toggleSummary')->assertSet('summaryOpen', false)
            ->assertDontSeeHtml('id="user-summary-content"')
            ->set('search', 'sample')->call('resetAll')
            ->assertSet('activityPerPage', 20)->assertSet('milestonePerPage', 5)->assertSet('summaryOpen', false);

        Livewire::actingAs($other)->test(ActivitiesDashboard::class)
            ->assertSet('activityPerPage', 10)->assertSet('milestonePerPage', 10)->assertSet('summaryOpen', true)
            ->set('activityPerPage', 100)->set('milestonePerPage', 50);

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->assertSet('activityPerPage', 20)->assertSet('milestonePerPage', 5)->assertSet('summaryOpen', false)
            ->call('toggleSummary')->assertSeeHtml('id="user-summary-content"');
        Livewire::actingAs($other)->test(ActivitiesDashboard::class)
            ->assertSet('activityPerPage', 100)->assertSet('milestonePerPage', 50)->assertSet('summaryOpen', true);
        $this->assertSame(['count' => 50], $user->preferences()->where('key', 'tables.per_page')->sole()->value);
    }

    public function test_both_paginators_are_independent_and_filters_reset_both(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        for ($i = 1; $i <= 12; $i++) {
            $this->assignedActivity($project, $user, '2026-08-30');
            ProjectMilestone::create([
                'project_id' => $project->id, 'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
                'cycle_year' => 2025, 'month' => 1, 'sequence' => $i + 1, 'percentage' => 1,
            ]);
        }

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->set('userFilter', (string) $user->id)
            ->set('activityPerPage', 5)->set('milestonePerPage', 5)
            ->assertSeeHtml('id="activity-per-page"')
            ->assertSeeHtml('id="milestone-per-page"')
            ->assertSeeHtml('app-pagination flex')
            ->assertSeeHtml('aria-current="page"')
            ->assertSeeHtml('aria-disabled="true"')
            ->assertViewHas('activities', fn ($items) => $items->total() === 12 && $items->count() === 5)
            ->assertViewHas('urgentMilestones', fn ($items) => $items->total() === 13 && $items->count() === 5)
            ->call('setPage', 2)->call('setPage', 3, 'milestonesPage')
            ->assertViewHas('activities', fn ($items) => $items->currentPage() === 2 && $items->count() === 5)
            ->assertViewHas('urgentMilestones', fn ($items) => $items->currentPage() === 3 && $items->count() === 3)
            ->call('toggleSummary')
            ->assertSet('paginators.page', 2)->assertSet('paginators.milestonesPage', 3)
            ->set('activityPerPage', 20)
            ->assertSet('paginators.page', 1)->assertSet('paginators.milestonesPage', 3)
            ->assertViewHas('activities', fn ($items) => $items->count() === 12)
            ->set('milestonePerPage', 10)->assertSet('paginators.milestonesPage', 1)
            ->call('setPage', 2, 'milestonesPage')->set('status', 'pending')
            ->assertSet('paginators.page', 1)->assertSet('paginators.milestonesPage', 1)
            ->assertViewHas('activities', fn ($items) => $items->total() === 0)
            ->assertViewHas('urgentMilestones', fn ($items) => $items->total() === 0);
    }

    public function test_page_size_preferences_only_accept_supported_values(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        UserPreference::create(['user_id' => $user->id, 'key' => 'activities.detail.per_page', 'value' => ['count' => 999]]);
        UserPreference::create(['user_id' => $user->id, 'key' => 'activities.milestones.per_page', 'value' => ['count' => 0]]);
        $component = Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->assertSet('activityPerPage', 10)->assertSet('milestonePerPage', 10);
        foreach ([5, 10, 20, 50, 100] as $count) {
            $component->set('activityPerPage', $count)->set('milestonePerPage', $count)
                ->assertViewHas('activities', fn ($items) => $items->perPage() === $count)
                ->assertViewHas('urgentMilestones', fn ($items) => $items->perPage() === $count);
        }
        $component->set('activityPerPage', 0)->set('milestonePerPage', 999)
            ->assertSet('activityPerPage', 10)->assertSet('milestonePerPage', 10);
    }

    public function test_filter_chips_can_be_removed_individually_and_clear_resets_all_filters(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $component = Livewire::actingAs($user)->test(ActivitiesDashboard::class);
        foreach ([
            'companyFilter' => ['CIESA'], 'yearSearch' => ['2026'], 'stateSearch' => ['Planning'],
            'typeOfProjectSearch' => ['Buildings'], 'investmentSearch' => ['Innovation'],
            'justificationSearch' => ['Normal Capex'], 'userFilter' => (string) $user->id,
            'projectFilter' => (string) $project->id, 'status' => 'overdue', 'search' => 'Assigned',
            'dateFrom' => '2025-01-01', 'dateTo' => '2026-12-31',
        ] as $key => $value) {
            $component->set($key, $value);
        }
        $component->assertViewHas('activeFilters', fn ($chips) => count($chips) === 11)
            ->call('removeFilter', 'search')->assertSet('search', '')->assertSet('status', 'overdue')
            ->call('removeFilter', 'due')->assertSet('dateFrom', '')->assertSet('dateTo', '')
            ->call('removeFilter', 'yearSearch', '2026')->assertSet('yearSearch', [])
            ->assertSet('companyFilter', ['CIESA'])->assertSet('userFilter', (string) $user->id)
            ->assertViewHas('activeFilters', fn ($chips) => count($chips) === 8)
            ->call('resetAll')->assertViewHas('activeFilters', [])
            ->assertViewHas('metrics', fn ($m) => $m['total'] === 1)
            ->assertViewHas('milestoneMetrics', fn ($m) => $m['total'] === 1)
            ->call('removeFilter', 'summaryOpen')->assertStatus(422);
    }

    public function test_status_and_due_filters_update_all_related_sections_consistently(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $this->assignedActivity($project, $user, '2026-08-30');
        $this->assignedActivity($project, $user, '2026-08-23', ['executed_at' => now()]);
        $this->assignedActivity($project, $user, '2026-09-06');
        foreach ([2 => null, 3 => now()] as $sequence => $executedAt) {
            ProjectMilestone::create([
                'project_id' => $project->id, 'milestone_id' => Milestone::where('code', 'WBS')->value('id'),
                'cycle_year' => 2026, 'month' => 8, 'sequence' => $sequence, 'percentage' => 10, 'executed_at' => $executedAt,
            ]);
        }

        $component = Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->set('userFilter', (string) $user->id)->set('dateFrom', '2026-08-01')->set('dateTo', '2026-08-31')
            ->assertViewHas('metrics', fn ($m) => $m['total'] === 2)
            ->assertViewHas('milestoneMetrics', fn ($m) => $m['total'] === 2);
        foreach (['overdue', 'completed'] as $status) {
            $component->set('status', $status)
                ->assertViewHas('metrics', fn ($m) => $m['total'] === 1 && $m[$status] === 1)
                ->assertViewHas('milestoneMetrics', fn ($m) => $m['total'] === 1 && $m[$status] === 1)
                ->assertViewHas('statusChart', fn ($chart) => array_sum($chart['series']) === 1)
                ->assertViewHas('milestoneStatusChart', fn ($chart) => array_sum($chart['series']) === 1)
                ->assertViewHas('userSummary', fn ($rows) => $rows->first()['total'] === 1)
                ->assertViewHas('activities', fn ($items) => $items->total() === 1);
        }
        $component->assertViewHas('urgentMilestones', fn ($items) => $items->total() === 0)
            ->assertViewHas('topOverdueActivities', fn ($items) => $items->isEmpty());
    }

    public function test_selecting_a_summary_user_creates_a_removable_filter_without_changing_other_filters(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $other = User::factory()->create();
        $other->assignRole('PROJECT MANAGER CIESA');
        $this->assignedActivity($project, $user, '2026-08-30');
        $this->assignedActivity($project, $other, '2026-08-30');

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->call('selectUser', $other->id)
            ->assertSet('userFilter', (string) $other->id)->assertSet('status', 'all')
            ->assertDispatched('activities-user-selected')
            ->assertViewHas('activeFilters', fn ($chips) => count($chips) === 1 && $chips[0]['label'] === 'User: '.$other->name)
            ->assertViewHas('activities', fn ($items) => $items->total() === 1)
            ->call('removeFilter', 'userFilter')->assertViewHas('activeFilters', [])
            ->assertViewHas('activities', fn ($items) => $items->total() === 3)
            ->set('status', 'overdue')->set('search', 'Assigned')
            ->call('selectUser', $other->id)->call('removeFilter', 'userFilter')
            ->assertSet('status', 'overdue')->assertSet('search', 'Assigned')
            ->assertViewHas('activities', fn ($items) => $items->total() === 2);

        $outsider = User::factory()->create();
        Livewire::actingAs($other)->test(ActivitiesDashboard::class)
            ->call('selectUser', $outsider->id)->assertForbidden();
    }

    public function test_management_focus_shows_stored_planning_week_and_project_search_replaces_the_selector(): void
    {
        $user = User::where('email', 'test@example.com')->sole();
        $project = $this->createProject($user);
        $this->assignedActivity($project, $user, '2021-01-03');

        Livewire::actingAs($user)->test(ActivitiesDashboard::class)
            ->assertDontSeeHtml('wire:model.live="projectFilter"')
            ->assertSee('Week 53 · 2020')
            ->set('search', $project->pda_code)
            ->assertViewHas('activities', fn ($items) => $items->total() === 2)
            ->assertViewHas('topOverdueActivities', fn ($items) => $items->contains(fn ($activity) => $activity->week_year === 2020 && $activity->week_number === 53));
    }

    private function assignedActivity(Project $project, User $user, string $dueDate, array $attributes = []): ProjectWeeklyActivity
    {
        $date = CarbonImmutable::parse($dueDate);

        return ProjectWeeklyActivity::create(array_merge([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'week_year' => $date->isoWeekYear,
            'week_number' => $date->isoWeek,
            'activity' => 'Assigned task '.uniqid(),
        ], $attributes));
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
