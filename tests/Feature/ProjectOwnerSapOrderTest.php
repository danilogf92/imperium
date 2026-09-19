<?php

namespace Tests\Feature;

use App\Livewire\Project\Create;
use App\Livewire\Project\Table;
use App\Models\Company;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectOwnerSapOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_sap_order_are_saved_and_displayed_in_projects(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $company = Company::where('company_code', 'CIESA')->sole();

        $secondCompany = Company::where('company_code', 'GRALCO')->sole();

        Livewire::actingAs($user)->test(Create::class)
            ->set('form.company_id', $company->id)
            ->set('newOwnerName', 'Danilo Perez')
            ->set('newOwnerCompanyIds', [$company->id, $secondCompany->id])
            ->call('createOwner')
            ->assertHasNoErrors()
            ->set('form.name', 'Owner fields project')
            ->set('form.pda_code', '26-999')
            ->set('form.rate', '1.05')
            ->set('form.state', 'Planning')
            ->set('form.investments', 'Innovation')
            ->set('form.justification', 'Normal Capex')
            ->set('form.classification_of_investments', 'Buildings')
            ->set('form.forecast_start_date', '2026-01-01')
            ->set('form.forecast_end_date', '2026-12-31')
            ->set('form.sap_order', 'SAP-450001')
            ->call('createProject')
            ->assertHasNoErrors();

        $owner = Owner::where('name', 'Danilo Perez')->sole();
        $projectId = (int) \App\Models\Project::where('name', 'Owner fields project')->value('id');

        $this->assertDatabaseHas('projects', [
            'name' => 'Owner fields project',
            'sap_order' => 'SAP-450001',
            'pda_code' => 'CIESA-26-999',
        ]);
        $this->assertDatabaseHas('company_owner', ['company_id' => $company->id, 'owner_id' => $owner->id]);
        $this->assertDatabaseHas('company_owner', ['company_id' => $secondCompany->id, 'owner_id' => $owner->id]);
        $this->assertDatabaseHas('owner_project', ['project_id' => $projectId, 'owner_id' => $owner->id]);

        Livewire::actingAs($user)->test(Table::class, ['active' => true])
            ->set('visibleColumns', ['name', 'owner', 'sap_order', 'actions'])
            ->assertSee('Danilo Perez')
            ->assertSee('SAP-450001');
    }
}
