<?php

namespace Tests\Feature;

use App\Livewire\Data\DataTable;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\Data\DataTableDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectDataColumnViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_column_views_are_saved_applied_and_isolated_by_project(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $first = $this->project($user, 'DATA-VIEW-1');
        $second = $this->project($user, 'DATA-VIEW-2');
        $columns = ['area', 'description', 'supplier', 'actions'];

        $component = Livewire::actingAs($user)->test(DataTable::class, ['project' => $first])
            ->set('visibleColumns', $columns)
            ->set('columnViewName', ' Supplier export ')
            ->call('saveColumnView')
            ->assertHasNoErrors()
            ->assertSee('Supplier export');
        $viewId = $component->get('selectedColumnView');

        $component->call('resetColumns')
            ->assertSet('selectedColumnView', '')
            ->assertSet('visibleColumns', DataTableDefinition::DEFAULT_COLUMNS)
            ->set('selectedColumnView', $viewId)
            ->assertSet('visibleColumns', $columns);

        $component->call('exportExcel')
            ->assertFileDownloaded('project-'.$first->id.'-data-view-1-data.xlsx');

        Livewire::actingAs($user)->test(DataTable::class, ['project' => $first])
            ->assertSet('selectedColumnView', $viewId)
            ->assertSee('Supplier export');

        Livewire::actingAs($user)->test(DataTable::class, ['project' => $second])
            ->assertDontSee('Supplier export')
            ->set('selectedColumnView', $viewId)
            ->assertSet('selectedColumnView', '')
            ->assertSet('visibleColumns', DataTableDefinition::DEFAULT_COLUMNS);

        $this->assertCount(1, UserPreference::where('user_id', $user->id)
            ->where('key', 'projects.data.column_views.v1.'.$first->id)->sole()->value);

        $component->call('deleteColumnView')
            ->assertSet('selectedColumnView', '')
            ->assertDontSee('Supplier export')
            ->assertSet('visibleColumns', $columns);
    }

    private function project(User $user, string $code): Project
    {
        return Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id,
            'name' => $code,
            'pda_code' => $code,
            'forecast_start_date' => '2026-01-01',
            'forecast_end_date' => '2027-12-31',
            'rate' => 1,
            'state' => 'Planning',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
        ]);
    }
}
