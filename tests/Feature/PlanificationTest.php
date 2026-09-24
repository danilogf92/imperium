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

    public function test_pda_code_column_displays_only_the_last_two_segments(): void
    {
        [$user, $project] = $this->projectContext();
        $project->update(['pda_code' => 'GRALCO-25-02']);

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.Livewire::actingAs($user)->test(Planification::class)->html());
        $code = (new \DOMXPath($document))->query('//div[@title="GRALCO-25-02"]')->item(0);

        $this->assertNotNull($code);
        $this->assertSame('25-02', trim($code->textContent));
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

    public function test_project_notes_can_be_created_edited_and_deleted_without_changing_filters(): void
    {
        [$user, $project] = $this->projectContext();
        $component = Livewire::actingAs($user)->test(Planification::class)
            ->set('search', $project->pda_code)
            ->call('openProjectActivities', $project->id)
            ->assertDispatched('open-modal', 'weekly-project-activity')
            ->set('weeklyActivity', '   ')->call('saveWeeklyActivity')->assertHasErrors('weeklyActivity')
            ->set('weeklyActivity', str_repeat('a', 5001))->call('saveWeeklyActivity')->assertHasErrors('weeklyActivity')
            ->set('weeklyActivity', "First note\nSecond line")->call('saveWeeklyActivity')->assertHasNoErrors()
            ->set('weeklyActivity', 'Another note')->call('saveWeeklyActivity')->assertHasNoErrors()
            ->assertSet('search', $project->pda_code)
            ->assertCount('weekActivities', 2)
            ->assertViewHas('plannedProjects', fn ($projects) => $projects->first()->planification_activities_count === 2);
        $note = $project->planificationActivities()->oldest('created_at')->get()->last();
        $this->assertEquals($user->id, $note->created_by);
        $component->call('editWeeklyActivity', $note->id)->set('weeklyActivity', 'Edited note')->call('saveWeeklyActivity')
            ->assertHasNoErrors()->assertSee('Edited note')
            ->call('requestDeleteWeeklyActivity', $note->id)->call('cancelDeleteWeeklyActivity');
        $this->assertModelExists($note);
        $component->call('requestDeleteWeeklyActivity', $note->id)->call('confirmDeleteWeeklyActivity')
            ->assertCount('weekActivities', 1)->assertSet('search', $project->pda_code);
        $this->assertModelMissing($note);
    }

    public function test_notes_are_scoped_to_project_and_permissions(): void
    {
        [$user, $project] = $this->projectContext();
        [, $other] = $this->projectContext();
        $otherNote = $other->planificationActivities()->create(['activity' => 'Private to another project', 'created_by' => $user->id]);
        try {
            Livewire::actingAs($user)->test(Planification::class)
                ->call('openProjectActivities', $project->id)->call('editWeeklyActivity', $otherNote->id);
            $this->fail('A note from another project was accessible.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertSame(\App\Models\ProjectWeeklyActivity::class, $exception->getModel());
        }

        $role = $user->roles()->where('company_id', $project->company_id)->firstOrFail();
        $role->revokePermissionTo(\App\Enums\ProjectPermissionEnum::Update->value);
        $role->revokePermissionTo(\App\Enums\ProjectPermissionEnum::Delete->value);
        Livewire::actingAs($user)->test(Planification::class)->call('openProjectActivities', $other->id)
            ->assertSee($otherNote->activity)->assertSet('canEditActivity', false)->assertSet('canDeleteActivity', false)
            ->set('weeklyActivity', 'Not allowed')->call('saveWeeklyActivity')->assertForbidden();
        Livewire::actingAs($user)->test(Planification::class)->call('openProjectActivities', $other->id)
            ->call('requestDeleteWeeklyActivity', $otherNote->id)->assertForbidden();
        $role->revokePermissionTo(\App\Enums\ProjectPermissionEnum::View->value);
        try {
            Livewire::actingAs($user)->test(Planification::class)->call('openProjectActivities', $other->id);
            $this->fail('Notes were accessible without view permission.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertSame(Project::class, $exception->getModel());
        }
        $this->assertModelExists($otherNote);
    }

    public function test_notes_tooltip_is_bounded_and_escapes_html(): void
    {
        [$user, $project] = $this->projectContext();
        for ($index = 0; $index < 5; $index++) {
            $project->planificationActivities()->create(['activity' => '<script>alert(1)</script> note '.$index, 'created_by' => $user->id]);
        }
        Livewire::actingAs($user)->test(Planification::class)
            ->assertViewHas('plannedProjects', fn ($projects) =>
                $projects->first()->planification_activities_count === 5 && $projects->first()->planificationActivities->count() === 3)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('href="'.route('projects.dashboard', $project->slug).'"', false)
            ->call('openProjectActivities', $project->id)->assertCount('weekActivities', 5);
    }

    public function test_notes_export_respects_filters_and_keeps_month_columns(): void
    {
        [$user, $project] = $this->projectContext();
        [, $other] = $this->projectContext();
        $project->planificationActivities()->create(['activity' => "=SUM(1,2)\nFirst note", 'created_by' => $user->id]);
        $project->planificationActivities()->create(['activity' => 'Second note', 'created_by' => $user->id]);
        $other->planificationActivities()->create(['activity' => 'Excluded note']);
        foreach (['combined', 'value'] as $mode) {
            $response = (new PlanificationExport)->download($user, [
                'search' => $project->pda_code, 'plants' => [], 'statuses' => [], 'creationYears' => [],
                'currency' => 'usd', 'cellDisplay' => $mode,
            ]);
            $path = $response->getFile()->getPathname();
            try {
                $book = IOFactory::load($path);
                $sheet = $book->getActiveSheet();
                $column = $sheet->getHighestDataColumn();
                $this->assertSame('Project Activities / Notes', $sheet->getCell($column.'1')->getValue());
                $this->assertSame('JAN', $sheet->getCell('I2')->getValue());
                $this->assertSame($project->pda_code, $sheet->getCell('C3')->getValue());
                $this->assertStringContainsString("=SUM(1,2)\nFirst note", $sheet->getCell($column.'3')->getValue());
                $this->assertStringContainsString('Second note', $sheet->getCell($column.'3')->getValue());
                $this->assertStringNotContainsString('Excluded note', $sheet->getCell($column.'3')->getValue());
                $this->assertSame('s', $sheet->getCell($column.'3')->getDataType());
                $this->assertTrue($sheet->getStyle($column.'3')->getAlignment()->getWrapText());
                if ($mode === 'value') {
                    $this->assertSame('=SUM(I3:I3)', $sheet->getCell('I4')->getValue());
                }
                $book->disconnectWorksheets();
            } finally {
                unlink($path);
            }
        }
    }

    public function test_activity_author_is_saved_preserved_displayed_and_exported(): void
    {
        [$user, $project] = $this->projectContext();
        Livewire::actingAs($user)->test(Planification::class)
            ->call('openWeeklyActivity', $project->id, 0)
            ->set('weeklyActivity', 'Authored activity')->call('saveWeeklyActivity')
            ->assertHasNoErrors()->assertSee($user->name);
        $activity = $project->weeklyActivities()->sole();
        $this->assertEquals($user->id, $activity->created_by);
        $editor = User::factory()->create(['name' => 'Different editor']);
        $editor->assignRole($user->roles()->where('company_id', $project->company_id)->firstOrFail());
        Livewire::actingAs($editor)->test(Planification::class)
            ->call('openWeeklyActivity', $project->id, 0)
            ->call('editWeeklyActivity', $activity->id)
            ->set('weeklyActivity', 'Edited activity')->call('saveWeeklyActivity')
            ->assertHasNoErrors()->assertSee($user->name)
            ->assertSet('weekActivities.0.attribution', $activity->attribution());
        $this->assertEquals($user->id, $activity->fresh()->created_by);
        $response = (new PlanificationExport)->download($user, [
            'search' => $project->pda_code, 'plants' => [], 'statuses' => [], 'creationYears' => [],
            'cellDisplay' => 'combined',
        ]);
        $path = $response->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $text = $book->getActiveSheet()->getCell('G3')->getValue();
            $this->assertStringContainsString($user->name, $text);
            $this->assertStringContainsString($activity->created_at->format('d/m/Y H:i'), $text);
            $this->assertStringContainsString('Edited activity', $text);
            $this->assertStringNotContainsString('Different editor', $text);
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_activity_migration_recovers_creation_author_from_audit(): void
    {
        [$user, $project] = $this->projectContext();
        $this->actingAs($user);
        $activity = $project->weeklyActivities()->create([
            'activity' => 'Legacy activity', 'week_year' => 2026, 'week_number' => 1,
        ]);
        $migration = require database_path('migrations/2026_09_19_150000_add_author_to_project_weekly_activities.php');
        $migration->down();
        $migration->up();
        $this->assertEquals($user->id, $activity->fresh()->created_by);
        $activity->update(['created_by' => null]);
        $this->assertStringContainsString(__('notes.unknown_author'), $activity->fresh()->attribution());
    }

    public function test_week_navigation_moves_both_columns_and_handles_year_boundaries(): void
    {
        [$user, $project] = $this->projectContext();
        $this->travelTo(now()->setDate(2026, 12, 28));
        $project->weeklyActivities()->create(['activity' => 'Week eleven task', 'week_year' => 2026, 'week_number' => 11]);

        Livewire::actingAs($user)->test(Planification::class)
            ->set('search', $project->pda_code)
            ->call('moveActivityWeek', 1)
            ->assertSet('activityWeekFilter', '2027-W01')->assertSet('creationYearFilter', [2027])
            ->assertViewHas('activityWeeks', fn ($weeks) => $weeks[0]['week'] === 1 && $weeks[0]['year'] === 2027 && $weeks[1]['week'] === 2)
            ->call('moveActivityWeek', -1)
            ->assertSet('activityWeekFilter', '2026-W53')->assertSet('creationYearFilter', [2026])
            ->assertViewHas('activityWeeks', fn ($weeks) => $weeks[0]['week'] === 53 && $weeks[1]['week'] === 1 && $weeks[1]['year'] === 2027)
            ->set('activityWeekFilter', '2026-W10')->call('setPage', 2)
            ->call('moveActivityWeek', 1)->assertSet('activityWeekFilter', '2026-W11')
            ->assertSet('paginators.page', 1)->assertSet('search', $project->pda_code)
            ->assertViewHas('plannedProjects', fn ($projects) => $projects->first()->weeklyActivities->first()->activity === 'Week eleven task')
            ->call('openWeeklyActivity', $project->id, 0)->assertSet('activityWeekNumber', 11)
            ->call('moveActivityWeek', 5)->assertStatus(422);
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
