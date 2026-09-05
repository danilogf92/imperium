<?php

namespace Tests\Feature;

use App\Livewire\Project\Table;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\Project\ProjectTableDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectColumnViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_views_persist_and_apply_columns_on_selection(): void
    {
        $user = User::factory()->create();
        $commercial = ['name', 'state', 'budgeted_euros', 'actions'];
        $data = ['pda_code', 'name', 'real_euros', 'executed_euros', 'actions'];
        $component = Livewire::actingAs($user)->test(Table::class, ['active' => true])
            ->set('visibleColumns', $commercial)
            ->set('columnViewName', ' Comercial ')
            ->call('saveColumnView')
            ->assertHasNoErrors()
            ->assertSet('columnViewName', '')
            ->assertSee('Comercial')
            ->assertDispatched('column-view-saved');
        $commercialId = $component->get('selectedColumnView');
        $savedCommercial = $component->get('visibleColumns');

        $component->set('visibleColumns', $data)
            ->assertSet('selectedColumnView', '')
            ->set('columnViewName', 'Data')
            ->call('saveColumnView')
            ->assertHasNoErrors();
        $dataId = $component->get('selectedColumnView');
        $savedData = $component->get('visibleColumns');

        $component->set('selectedColumnView', $commercialId)
            ->assertSet('visibleColumns', $savedCommercial);

        Livewire::actingAs($user)->test(Table::class, ['active' => true])
            ->assertSet('selectedColumnView', $commercialId)
            ->assertSet('visibleColumns', $savedCommercial)
            ->assertSee('Comercial')
            ->assertSee('Data')
            ->set('selectedColumnView', $dataId)
            ->assertSet('visibleColumns', $savedData)
            ->call('resetColumns')
            ->assertSet('selectedColumnView', '')
            ->assertSet('visibleColumns', ProjectTableDefinition::DEFAULT_COLUMNS)
            ->set('selectedColumnView', $commercialId)
            ->assertSet('visibleColumns', $savedCommercial);
    }

    public function test_deleting_a_view_preserves_current_columns_and_other_views(): void
    {
        $user = User::factory()->create();
        $component = Livewire::actingAs($user)->test(Table::class, ['active' => true])
            ->set('columnViewName', 'Keep this view')->call('saveColumnView')
            ->set('visibleColumns', ['name', 'booked', 'actions'])
            ->set('columnViewName', 'Remove this view')->call('saveColumnView');
        $columns = $component->get('visibleColumns');

        $component->assertSee('Delete view')->call('deleteColumnView')
            ->assertSet('selectedColumnView', '')
            ->assertSet('visibleColumns', $columns)
            ->assertDontSee('Remove this view')
            ->assertSee('Keep this view')
            ->assertDispatched('column-view-deleted');

        Livewire::actingAs($user)->test(Table::class, ['active' => true])
            ->assertSet('visibleColumns', $columns)
            ->assertDontSee('Remove this view')
            ->assertSee('Keep this view');

        $component->call('deleteColumnView');
        $this->assertCount(1, UserPreference::where('user_id', $user->id)
            ->where('key', 'projects.table.column_views.v1')->sole()->value);
    }

    public function test_view_names_are_validated_without_overwriting_existing_views(): void
    {
        $user = User::factory()->create();
        $component = Livewire::actingAs($user)->test(Table::class)
            ->set('columnViewName', '   ')
            ->call('saveColumnView')
            ->assertHasErrors(['columnViewName' => 'required'])
            ->set('columnViewName', str_repeat('x', 61))
            ->call('saveColumnView')
            ->assertHasErrors(['columnViewName' => 'max'])
            ->set('columnViewName', 'Comercial')
            ->call('saveColumnView')
            ->assertHasNoErrors();

        $component->set('columnViewName', 'comercial')
            ->call('saveColumnView')
            ->assertHasErrors('columnViewName');

        $views = UserPreference::where('user_id', $user->id)
            ->where('key', 'projects.table.column_views.v1')->sole()->value;
        $this->assertCount(1, $views);
        $this->assertSame('Comercial', array_values($views)[0]['name']);
    }

    public function test_users_cannot_load_or_change_other_users_views(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $component = Livewire::actingAs($owner)->test(Table::class)
            ->set('columnViewName', 'Private commercial view')
            ->call('saveColumnView');
        $id = $component->get('selectedColumnView');

        $otherComponent = Livewire::actingAs($other)->test(Table::class, ['active' => true]);
        $initialColumns = $otherComponent->get('visibleColumns');
        $otherComponent->assertDontSee('Private commercial view')
            ->set('selectedColumnView', $id)
            ->assertSet('selectedColumnView', '')
            ->assertSet('visibleColumns', $initialColumns)
            ->set('columnViewName', 'Private commercial view')
            ->call('saveColumnView')
            ->assertHasNoErrors();

        $this->assertSame(2, UserPreference::where('key', 'projects.table.column_views.v1')->count());
    }
}
