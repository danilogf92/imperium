<?php

namespace Tests\Feature;

use App\Livewire\Data\DataTable;
use App\Models\Company;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_suppliers_can_be_created_selected_saved_and_reused_with_plant_isolation(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $company = Company::where('company_code', 'CIESA')->sole();
        $project = Project::create([
            'company_id' => $company->id, 'created_by' => $user->id,
            'name' => 'Supplier test', 'pda_code' => 'SUPPLIER-01', 'rate' => 1,
            'state' => 'Planning', 'investments' => 'Innovation', 'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2026-01-01', 'forecast_end_date' => '2027-12-31',
        ]);
        Supplier::create(['company_id' => Company::where('id', '<>', $company->id)->value('id'), 'name' => 'Other plant supplier']);
        $component = Livewire::actingAs($user)->test(DataTable::class, ['project' => $project])
            ->call('openCreateModal')->call('toggleSupplierCreator')
            ->set('newSupplierName', '  Test supplier  ')->call('createSupplier')
            ->assertHasNoErrors()->assertSet('editData.supplier', 'Test supplier')
            ->assertSet('showSupplierCreator', false)
            ->assertViewHas('supplierOptions', fn ($options) => collect($options)->contains('value', 'Test supplier')
                && ! collect($options)->contains('value', 'Other plant supplier'))
            ->call('createData')->assertHasNoErrors();
        $row = $project->data()->sole();
        $this->assertSame('Test supplier', $row->supplier);
        $component->call('openEditModal', $row->id)->assertSet('editData.supplier', 'Test supplier')
            ->set('editData.supplier', 'Other plant supplier')->call('updateData')->assertHasErrors('editData.supplier')
            ->set('editData.supplier', 'Test supplier')->call('updateData')->assertHasNoErrors();
        $component->call('openCreateModal')->call('toggleSupplierCreator')
            ->set('newSupplierName', 'test supplier')->call('createSupplier')->assertHasErrors('newSupplierName');
        $this->assertSame(1, Supplier::where('company_id', $company->id)->count());

        $row->update(['supplier' => 'Imported supplier']);
        $component->call('closeEditModal')->call('openEditModal', $row->id)
            ->assertViewHas('supplierOptions', fn ($options) => collect($options)->contains('value', 'Imported supplier'))
            ->call('updateData')->assertHasNoErrors();

        $user->syncRoles([]);
        $component->call('createSupplier')->assertForbidden();
    }
}
