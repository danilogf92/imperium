<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Review;
use App\Models\Company;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_shows_four_state_charts_and_uses_dashboard_filters(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();

        foreach ([['Planning', '2026-01-01', 100], ['Execution', '2025-01-01', 200]] as [$state, $date, $budget]) {
            $project = Project::create([
                'company_id' => Company::where('company_code', 'CIESA')->value('id'),
                'created_by' => $user->id,
                'name' => "Review {$state} project",
                'pda_code' => "REVIEW-{$state}",
                'rate' => 1,
                'state' => $state,
                'investments' => 'Innovation',
                'justification' => 'Normal Capex',
                'classification_of_investments' => 'Buildings',
                'forecast_start_date' => $date,
                'forecast_end_date' => '2027-12-31',
            ]);
            Data::create(['project_id' => $project->id, 'global_price_euros' => $budget]);
        }

        $this->actingAs($user)->get('/review')->assertOk()
            ->assertSee('Projects by state')
            ->assertSee('Budget by state')
            ->assertSee('Project status count')
            ->assertSee('Project status value');

        Livewire::actingAs($user)->test(Review::class)
            ->assertViewHas('hasProjects', true)
            ->assertViewHas('hasFinancialData', true)
            ->set('yearSearch', ['2026'])
            ->assertViewHas('hasProjects', true)
            ->set('stateSearch', ['Execution'])
            ->assertViewHas('hasProjects', false)
            ->call('resetAll')
            ->assertSet('yearSearch', [])
            ->assertSet('stateSearch', [])
            ->assertViewHas('hasProjects', true);
    }
}
