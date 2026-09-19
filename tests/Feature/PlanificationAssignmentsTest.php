<?php

namespace Tests\Feature;

use App\Enums\ProjectPermissionEnum;
use App\Exports\PlanificationExport;
use App\Livewire\Planification\Planification;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectWeeklyActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PlanificationAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $this->seed();
        $creator = User::where('email', 'test@example.com')->sole();
        $company = Company::where('company_code', 'CIESA')->sole();
        $recipient = User::factory()->create(['is_active' => true, 'name' => 'Activity recipient']);
        $recipient->assignRole($creator->roles()->where('company_id', $company->id)->firstOrFail());
        $project = Project::create([
            'company_id' => $company->id, 'created_by' => $creator->id,
            'name' => 'Assigned project', 'pda_code' => 'ASSIGN-01', 'rate' => 1,
            'state' => 'Planning', 'investments' => 'Innovation', 'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2026-01-01', 'forecast_end_date' => '2027-12-31',
        ]);

        return [$creator, $recipient, $project];
    }

    public function test_planning_and_summary_urls_and_legacy_redirects(): void
    {
        [$creator] = $this->context();
        $this->assertSame('/planning', route('planification', [], false));
        $this->assertSame('/summary', route('resume', [], false));
        $this->actingAs($creator)->get('/planning')->assertOk();
        $this->get('/summary')->assertOk();
        $this->get('/planification')->assertStatus(301)->assertRedirect('/planning');
        $this->get('/resume')->assertStatus(301)->assertRedirect('/summary');
    }

    public function test_assignment_creation_edit_reassignment_and_removal_notifications(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $component = Livewire::actingAs($creator)->test(Planification::class)
            ->call('openProjectActivities', $project->id)
            ->set('weeklyActivity', 'Review supplier offer')->set('activityPeriod', '2026-W39')
            ->set('activityAssigneeId', $recipient->id)->call('saveWeeklyActivity')->assertHasNoErrors();
        $activity = $project->weeklyActivities()->sole();
        $this->assertEquals($creator->id, $activity->created_by);
        $this->assertEquals($recipient->id, $activity->assigned_to);
        $notice = $recipient->unreadPlanificationAssignments()->sole();
        $this->assertSame('2026-W39', $notice->data['period']);
        $this->assertSame($creator->name, $notice->data['assigned_by']);

        $component->call('editWeeklyActivity', $activity->id)->set('weeklyActivity', 'Updated supplier offer')
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $this->assertSame(1, $recipient->notifications()->count());
        $component->call('editWeeklyActivity', $activity->id)->set('activityAssigneeId', $creator->id)
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $this->assertSame(0, $recipient->notifications()->count());
        $this->assertSame(1, $creator->notifications()->count());
        $component->call('editWeeklyActivity', $activity->id)->set('activityAssigneeId', null)
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $this->assertNull($activity->fresh()->assigned_to);
        $this->assertSame(0, $creator->notifications()->count());
        $this->assertEquals($creator->id, $activity->fresh()->created_by);
    }

    public function test_cross_plant_inactive_and_unauthorized_assignments_are_rejected(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $outsider = User::factory()->create(['is_active' => true]);
        $outsider->assignRole($creator->roles()->where('company_id', '<>', $project->company_id)->firstOrFail());
        $inactive = User::factory()->create(['is_active' => false]);
        $inactive->assignRole($recipient->roles()->first());
        $component = Livewire::actingAs($creator)->test(Planification::class)
            ->call('openProjectActivities', $project->id)
            ->assertViewHas('assigneeOptions', fn ($users) => $users->contains('id', $recipient->id)
                && ! $users->contains('id', $outsider->id) && ! $users->contains('id', $inactive->id))
            ->set('weeklyActivity', 'Restricted assignment');
        foreach ([$outsider->id, $inactive->id] as $id) {
            $component->set('activityAssigneeId', $id)->call('saveWeeklyActivity')->assertHasErrors('activityAssigneeId');
        }
        $component->set('activityAssigneeId', null)->set('activityPeriod', '2026-W99')
            ->call('saveWeeklyActivity')->assertHasErrors('activityPeriod');
        $this->assertSame(0, $project->weeklyActivities()->count());
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_assignment_is_visible_after_login_and_read_only_for_its_recipient(): void
    {
        [$creator, $recipient, $project] = $this->context();
        Livewire::actingAs($creator)->test(Planification::class)->call('openProjectActivities', $project->id)
            ->set('weeklyActivity', 'Inbox activity')->set('activityAssigneeId', $recipient->id)
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $activity = $project->weeklyActivities()->sole();
        $this->actingAs($recipient)->get('/planning')->assertOk()
            ->assertSee('Inbox activity')->assertSee('1 new assignments')->assertSee($creator->name);
        Livewire::actingAs($recipient)->test(Planification::class)
            ->assertViewHas('assignedActivities', fn ($rows) => $rows->count() === 1)
            ->call('openAssignedActivity', $activity->id)->assertHasNoErrors()
            ->assertSet('allProjectActivities', true)->assertSet('activityProjectId', $project->id);
        $this->assertSame(0, $recipient->unreadPlanificationAssignments()->count());
        $this->assertSame(1, $recipient->planificationAssignments()->count());
        try {
            Livewire::actingAs($creator)->test(Planification::class)->call('openAssignedActivity', $activity->id);
            $this->fail('Another user opened a private assignment notification');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertSame(ProjectWeeklyActivity::class, $exception->getModel());
        }
    }

    public function test_completed_assignments_leave_pending_inbox_and_return_when_reopened(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $this->actingAs($creator);
        $activity = app(\App\Services\Planification\PlanificationActivityService::class)
            ->save($project, null, 'Complete assigned work', $recipient->id, 2026, 39);

        $component = Livewire::actingAs($recipient)->test(Planification::class)
            ->assertViewHas('assignedActivities', fn ($rows) => $rows->contains('id', $activity->id))
            ->call('openAssignedActivity', $activity->id)
            ->call('toggleWeeklyActivityExecuted', $activity->id)
            ->assertHasNoErrors()
            ->assertDispatched('planification-notifications-updated')
            ->assertDispatched('alert', fn ($event, $params) => $params['type'] === 'success'
                && $params['title'] === __('planification_activities.completed_feedback'))
            ->assertViewHas('assignedActivities', fn ($rows) => $rows->isEmpty());
        $this->assertNotNull($activity->fresh()->executed_at);
        $this->assertEquals($recipient->id, $activity->fresh()->assigned_to);
        $this->assertSame(0, $recipient->planificationAssignments()->count());
        $this->assertSame(0, $recipient->unreadPlanificationAssignments()->count());
        $this->assertSame(1, $recipient->notifications()->count());

        $component->call('toggleWeeklyActivityExecuted', $activity->id)
            ->assertHasNoErrors()
            ->assertViewHas('assignedActivities', fn ($rows) => $rows->contains('id', $activity->id));
        $this->assertNull($activity->fresh()->executed_at);
        $this->assertSame(1, $recipient->notifications()->count());
    }

    public function test_name_notes_exclude_activities_and_show_only_note_metadata(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $this->actingAs($creator);
        $service = app(\App\Services\Planification\PlanificationActivityService::class);
        $note = $service->save($project, null, 'Project note text', null, null, null);
        $service->save($project, null, 'Weekly work', null, 2026, 39);
        $service->save($project, null, 'Assigned work', $recipient->id, null, null);
        $component = Livewire::test(Planification::class)
            ->call('openProjectNotes', $project->id)
            ->assertSet('notesOnly', true)->assertCount('weekActivities', 1)
            ->assertSet('weekActivities.0.id', $note->id)
            ->set('weeklyActivity', 'Another note')->call('saveWeeklyActivity')
            ->assertHasNoErrors()->assertCount('weekActivities', 2);
        $this->assertSame(2, $project->planificationNotes()->count());
        $component->call('openProjectActivities', $project->id)
            ->assertSet('notesOnly', false)->assertCount('weekActivities', 4);
    }

    public function test_existing_notes_are_migrated_without_losing_author_or_dates(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $migration = require database_path('migrations/2026_09_19_160000_unify_planification_activities_and_assignments.php');
        $migration->down();
        DB::table('project_notes')->insert([
            'project_id' => $project->id, 'created_by' => $creator->id, 'body' => 'Existing project note',
            'created_at' => '2026-09-01 10:30:00', 'updated_at' => '2026-09-02 11:00:00',
        ]);
        $migration->up();
        $this->assertFalse(Schema::hasTable('project_notes'));
        $activity = $project->weeklyActivities()->sole();
        $this->assertSame('Existing project note', $activity->activity);
        $this->assertEquals($creator->id, $activity->created_by);
        $this->assertSame('2026-09-01 10:30:00', $activity->created_at->format('Y-m-d H:i:s'));
        $this->assertNull($activity->week_year);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_export_includes_period_creator_assignee_and_status(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $this->actingAs($creator);
        $activity = app(\App\Services\Planification\PlanificationActivityService::class)
            ->save($project, null, 'Export assigned activity', $recipient->id, 2026, 39);
        $activity->update(['executed_at' => now()]);
        $response = (new PlanificationExport)->download($creator, [
            'search' => $project->pda_code, 'plants' => [], 'statuses' => [], 'creationYears' => [],
            'activityWeeks' => '2026-W39', 'cellDisplay' => 'combined',
        ]);
        $path = $response->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $sheet = $book->getActiveSheet();
            foreach (['G', $sheet->getHighestDataColumn()] as $column) {
                $text = $sheet->getCell($column.'3')->getValue();
                foreach (['2026-W39', 'Created by: ', $creator->name, 'Assigned to: Activity recipient', 'Completed', 'Export assigned activity'] as $expected) {
                    $this->assertStringContainsString($expected, $text);
                }
            }
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_deletion_cleans_notifications_and_removed_access_hides_assignments(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $component = Livewire::actingAs($creator)->test(Planification::class)
            ->call('openProjectActivities', $project->id)
            ->set('weeklyActivity', 'Delete assigned activity')->set('activityAssigneeId', $recipient->id)
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $activity = $project->weeklyActivities()->sole();
        $this->assertSame(1, $recipient->unreadPlanificationAssignments()->count());
        $recipient->syncRoles([]);
        $this->assertSame(0, $recipient->unreadPlanificationAssignments()->count());
        $this->assertSame(0, $recipient->assignedPlanificationActivities()->count());
        $component->call('requestDeleteWeeklyActivity', $activity->id)->call('confirmDeleteWeeklyActivity')
            ->assertHasNoErrors();
        $this->assertModelMissing($activity);
        $this->assertSame(0, $recipient->notifications()->count());
    }

    public function test_week_context_is_preserved_and_editing_can_clear_the_assignee(): void
    {
        [$creator, $recipient, $project] = $this->context();
        $component = Livewire::actingAs($creator)->test(Planification::class)
            ->set('activityWeekFilter', '2026-W39')->call('openWeeklyActivity', $project->id, 1)
            ->assertSet('activityPeriod', '2026-W40')
            ->set('weeklyActivity', 'Next week task')->set('activityAssigneeId', $recipient->id)
            ->call('saveWeeklyActivity')->assertHasNoErrors()->assertCount('weekActivities', 1);
        $activity = $project->weeklyActivities()->sole();
        $this->assertSame(40, $activity->week_number);
        $component->call('editWeeklyActivity', $activity->id)->set('activityAssigneeId', '')
            ->call('saveWeeklyActivity')->assertHasNoErrors();
        $this->assertNull($activity->fresh()->assigned_to);
        $this->assertSame(0, $recipient->notifications()->count());
    }
}
