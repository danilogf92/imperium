<?php

namespace Tests\Feature;

use App\Exports\ProjectResumeExport;
use App\Livewire\Resume\Resume;
use App\Models\Company;
use App\Models\Data;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ResumeFinancialTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_financial_values_charts_and_export_match_in_both_currencies(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $this->project($user, 2026, 'Execution', 100, 80, 30);
        $this->project($user, 2026, 'Planning', 50, 0, 0);
        $this->project($user, 2025, 'Execution', 100, 30, 70);
        $this->project($user, 2026, 'Postponed', 1000, 900, 800);
        $component = Livewire::actingAs($user)->test(Resume::class);

        $component->assertViewHas('rows', fn ($rows) => $rows->count() === 2 && $rows->first()['committed'] === -40.0)
            ->assertViewHas('comparisonChartOptions', fn ($chart) => $chart['yaxis']['min'] <= -40)
            ->assertViewHas('projectsChartOptions', fn ($chart) => array_column($chart['series'], 'name') === [
                'Projects', 'Budgeted', 'Assigned', 'Executed',
            ] && array_column($chart['series'], 'type') === ['column', 'line', 'line', 'line']
                && $chart['series'][2]['data'] === [30.0, 80.0]
                && $chart['yaxis'][0]['seriesName'] === 'Projects'
                && $chart['yaxis'][1]['opposite'] === true);

        foreach (['euro' => [1, '€'], 'dollar' => [2, '$']] as $currency => [$rate, $symbol]) {
            $component->set('yearFilter', ['2026'])->set('currency', $currency)
                ->assertViewHas('rows', function ($rows) use ($rate): bool {
                    $row = $rows->sole();

                    return $row['project_count'] === 2
                        && $row['budgeted'] === 150.0 * $rate
                        && $row['approved'] === 100.0 * $rate
                        && $row['assigned'] === 80.0 * $rate
                        && $row['booked'] === 30.0 * $rate
                        && $row['committed'] === 50.0 * $rate
                        && $row['available'] === 20.0 * $rate;
                })
                ->assertViewHas('comparisonChartOptions', fn ($chart) => array_column($chart['series'], 'name') === [
                    'Budgeted', 'Approved', 'Booked (Real SAP)', 'Committed', 'Available',
                ] && $chart['series'][3]['data'] === [50.0 * $rate])
                ->assertViewHas('averageChartOptions', fn ($chart) => $chart['series'][3]['data'] === [25.0 * $rate])
                ->assertViewHas('coverageChartOptions', fn ($chart) => $chart['series'][1]['data'] === [30.0]
                    && $chart['series'][2]['data'] === [50.0]
                    && $chart['series'][3]['data'] === [20.0]);

            $rows = $component->viewData('rows');
            $response = (new ProjectResumeExport)->download($rows, ['Currency' => $currency], $symbol);
            $path = $response->getFile()->getPathname();
            try {
                $workbook = IOFactory::load($path);
                $sheet = $workbook->getActiveSheet();
                $this->assertSame(['Year', 'Number of Projects', 'Budgeted '.$symbol, 'Approved '.$symbol,
                    'Booked (Real SAP) '.$symbol, 'Committed '.$symbol, 'Available '.$symbol], $sheet->rangeToArray('A6:G6')[0]);
                $this->assertEquals([2026, 2, 150 * $rate, 100 * $rate, 30 * $rate, 50 * $rate, 20 * $rate],
                    $sheet->rangeToArray('A7:G7', null, false, false)[0]);
                $this->assertSame('A6:G7', $sheet->getAutoFilter()->getRange());
                $workbook->disconnectWorksheets();
            } finally {
                unlink($path);
            }
        }

        $component->set('yearFilter', ['2099'])
            ->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
    }

    public function test_cash_flow_limits_months_to_selected_year_and_summarizes_extra_years(): void
    {
        $this->seed();
        $this->travelTo(now()->setDate(2026, 9, 5));
        $user = User::where('email', 'test@example.com')->sole();
        $this->project($user, 2026, 'Execution', 1000, 0, 0);
        $project = Project::sole();
        foreach ([[2026, 8, 20], [2026, 9, 30], [2027, 1, 50]] as $index => [$year, $month, $percentage]) {
            ProjectMilestone::create([
                'project_id' => $project->id,
                'milestone_id' => Milestone::where('code', 'PO')->value('id'),
                'cycle_year' => $year,
                'month' => $month,
                'percentage' => $percentage,
                'sequence' => $index + 1,
                'executed_at' => $index === 0 ? '2026-09-04 12:00:00' : null,
            ]);
        }

        $component = Livewire::actingAs($user)->test(Resume::class)->set('yearFilter', ['2026']);
        foreach (['euro' => 1, 'dollar' => 2] as $currency => $rate) {
            $component->set('currency', $currency)
                ->assertViewHas('cashFlowSummary', fn ($summary) => $summary['total'] === 500.0 * $rate
                    && $summary['outside_total'] === 500.0 * $rate && $summary['outside_years'] === '2027')
                ->assertViewHas('cashFlowChartOptions', function ($chart) use ($rate): bool {
                    $bars = $chart['series'][0]['data'];

                    return count($bars) === 12
                        && $chart['xaxis']['categories'][0] === 'Jan 2026'
                        && $chart['xaxis']['categories'][11] === 'Dec 2026'
                        && $bars[7] === 200.0 * $rate
                        && $chart['colors'] === ['#94A3B8', '#38BDF8']
                        && $bars[8] === 300.0 * $rate
                        && $chart['series'][0]['name'] === 'Planning'
                        && $chart['series'][1]['name'] === 'Real'
                        && $chart['series'][1]['data'] === array_fill(0, 12, 0.0)
                        && $chart['chart']['stacked'] === false
                        && $chart['chart']['type'] === 'bar'
                        && $chart['chart']['height'] === 300
                        && ! isset($chart['annotations'])
                        && ! isset($chart['yaxis']['max'])
                        && $chart['yaxis']['forceNiceScale'] === true;
                })
                ->assertViewHas('plannedCashFlowChartOptions', function ($chart) use ($rate): bool {
                    $bars = $chart['series'][0]['data'];

                    return count($bars) === 12
                        && $bars[7]['y'] === 200.0 * $rate
                        && $bars[7]['fillColor'] === '#F97316'
                        && $bars[8]['y'] === 300.0 * $rate
                        && $bars[8]['fillColor'] === '#7DD3FC'
                        && $chart['colors'] === ['#7DD3FC', '#94A3B8']
                        && $chart['plotOptions']['bar']['distributed'] === false
                        && count($chart['series'][1]['data']) === 12;
                })
                ->assertViewHas('plannedCashFlowSummary', fn ($summary) => $summary['total'] === 500.0 * $rate
                    && $summary['outside_total'] === 500.0 * $rate);
        }

        $component->set('yearFilter', [])
            ->assertViewHas('cashFlowSummary', fn ($summary) => $summary['total'] === 2000.0 && $summary['outside_total'] === 0.0)
            ->assertViewHas('cashFlowChartOptions', fn ($chart) => count($chart['series'][0]['data']) === 24);

        ProjectMilestone::where('cycle_year', 2026)->delete();
        $component->set('yearFilter', ['2026'])
            ->assertViewHas('cashFlowSummary', fn ($summary) => $summary['total'] === 0.0 && $summary['outside_total'] === 1000.0)
            ->assertViewHas('cashFlowChartOptions', fn ($chart) => count($chart['series'][0]['data']) === 12)
            ->assertDontSee('monthly-milestone-cash-flow');
    }

    public function test_cash_flow_comparison_uses_accounting_month_and_keeps_undated_amounts_separate(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $this->project($user, 2026, 'Execution', 1000, 0, 25);
        $project = Project::sole();
        foreach ([['2026-08-15', 100], ['2026-08-20', -20], ['2027-01-01', 50]] as [$date, $amount]) {
            Data::create(['project_id' => $project->id, 'accounting_date' => $date,
                'document_date' => '2026-02-01', 'real_value_euros' => $amount, 'real_value' => $amount * 2]);
        }
        $this->project($user, 2025, 'Execution', 100, 0, 999);
        $component = Livewire::actingAs($user)->test(Resume::class)->set('yearFilter', ['2026']);
        foreach (['euro' => 1, 'dollar' => 2] as $currency => $rate) {
            $component->set('currency', $currency)
                ->assertDontSee('Planned milestone cash flow test')
                ->assertViewHas('cashFlowChartOptions', fn ($chart) =>
                    $chart['series'][1]['data'][1] === 130.0 * $rate
                    && $chart['series'][1]['data'][7] === 0.0
                    && count($chart['series'][1]['data']) === 12
                    && $chart['plotOptions']['bar']['distributed'] === false
                    && $chart['tooltip']['shared'] === true
                    && $chart['tooltip']['intersect'] === false
                    && $chart['tooltip']['hideEmptySeries'] === false
                    && $chart['tooltip']['x']['show'] === true)
                ->assertViewHas('plannedCashFlowChartOptions', fn ($chart) =>
                    $chart['series'][1]['data'][7]['y'] === 80.0 * $rate
                    && $chart['series'][1]['data'][1]['y'] === 0.0
                    && array_column($chart['series'][0]['data'], 'x') === array_column($chart['series'][1]['data'], 'x')
                    && ! isset($chart['yaxis']['min'])
                    && $chart['chart']['stacked'] === false)
                ->assertViewHas('plannedCashFlowSummary', fn ($summary) =>
                    $summary['actual_total'] === 80.0 * $rate
                    && $summary['outside_actual'] === 50.0 * $rate
                    && $summary['undated_total'] === 25.0 * $rate);
        }
    }

    public function test_cash_flow_planning_and_document_values_share_all_project_filters_and_permissions(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $this->project($user, 2026, 'Execution', 1000, 0, 0);
        $selected = Project::sole();
        $this->project($user, 2025, 'Planning', 9000, 0, 0);
        $other = Project::whereKeyNot($selected->id)->sole();
        $other->update([
            'company_id' => Company::where('company_code', 'GRALCO')->value('id'),
            'investments' => 'Maintenance', 'classification_of_investments' => 'Land', 'justification' => 'Special Project',
        ]);
        foreach ([$selected, $other] as $project) {
            ProjectMilestone::create([
                'project_id' => $project->id, 'milestone_id' => Milestone::where('code', 'PO')->value('id'),
                'cycle_year' => 2026, 'month' => 2, 'percentage' => 100, 'sequence' => 1,
            ]);
            Data::create([
                'project_id' => $project->id, 'document_date' => '2026-02-15', 'accounting_date' => '2026-06-01',
                'real_value_euros' => $project->id === $selected->id ? -20 : 900, 'real_value' => $project->id === $selected->id ? -40 : 1800,
            ]);
        }
        foreach ([
            'search' => $selected->pda_code, 'plantFilter' => [(string) $selected->company_id],
            'yearFilter' => ['2026'], 'stateFilter' => ['Execution'], 'investmentFilter' => ['Innovation'],
            'classificationFilter' => ['Buildings'], 'justificationFilter' => ['Normal Capex'],
        ] as $filter => $value) {
            Livewire::actingAs($user)->test(Resume::class)->set($filter, $value)
                ->assertViewHas('cashFlowChartOptions', fn ($chart) =>
                    $chart['series'][0]['data'][1] === 1000.0 && $chart['series'][1]['data'][1] === -20.0
                    && $chart['series'][1]['data'][5] === 0.0 && ! isset($chart['yaxis']['min']));
        }
        $viewer = User::factory()->create();
        $viewer->assignRole('PROJECT MANAGER CIESA');
        Livewire::actingAs($viewer)->test(Resume::class)
            ->assertViewHas('cashFlowChartOptions', fn ($chart) =>
                $chart['series'][0]['data'][1] === 1000.0 && $chart['series'][1]['data'][1] === -20.0);
    }

    public function test_new_projection_reuses_filtered_document_values_and_preserves_existing_charts(): void
    {
        $this->seed();
        $this->travelTo(now()->setDate(2026, 9, 23));
        $user = User::where('email', 'test@example.com')->sole();
        $this->project($user, 2026, 'Execution', 1000, 0, 50);
        $project = Project::sole();
        foreach ([[7, 20], [9, 30]] as $index => [$month, $percentage]) {
            ProjectMilestone::create([
                'project_id' => $project->id, 'milestone_id' => Milestone::where('code', 'PO')->value('id'),
                'cycle_year' => 2026, 'month' => $month, 'percentage' => $percentage, 'sequence' => $index + 1,
            ]);
        }
        Data::create(['project_id' => $project->id, 'document_date' => '2026-08-15', 'accounting_date' => '2026-09-15', 'real_value_euros' => 100, 'real_value' => 200]);
        $this->project($user, 2025, 'Execution', 5000, 0, 999);

        $component = Livewire::actingAs($user)->test(Resume::class)->set('yearFilter', ['2026']);
        foreach (['euro' => 1, 'dollar' => 2] as $currency => $rate) {
            $component->set('currency', $currency)
                ->assertSee('Cash flow: Planning, Real and Projected')
                ->assertViewHas('projectionChartOptions', fn ($chart) => count($chart['series']) === 3
                    && $chart['chart']['stacked'] === false
                    && count($chart['series'][2]['data']) === 12
                    && $chart['series'][2]['data'][7] === null
                    && $chart['series'][2]['data'][8] === 325.0 * $rate)
                ->assertViewHas('projectionSummaries', fn ($items) => $items[0]['closed_planned'] === 200.0 * $rate
                    && $items[0]['closed_actual'] === 100.0 * $rate && $items[0]['remaining_months'] === 4)
                ->assertViewHas('cashFlowChartOptions', fn ($chart) => count($chart['series']) === 2 && $chart['series'][1]['data'][7] === 100.0 * $rate)
                ->assertViewHas('plannedCashFlowChartOptions', fn ($chart) => count($chart['series']) === 2 && $chart['series'][1]['data'][8]['y'] === 100.0 * $rate);
        }
        $component->set('search', 'no-such-project')
            ->assertViewHas('projectionSummaries', fn ($items) => $items[0]['closed_actual'] === 0.0 && $items[0]['closed_planned'] === 0.0);
    }

    private function project(User $user, int $year, string $state, float $budget, float $assigned, float $sap): void
    {
        $project = Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id,
            'name' => 'Resume project '.uniqid(),
            'pda_code' => 'RESUME-'.uniqid(),
            'rate' => 2,
            'state' => $state,
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => $year.'-01-01',
            'forecast_end_date' => ($year + 1).'-12-31',
            'approve_date' => '2024-01-01',
        ]);
        Data::create([
            'project_id' => $project->id,
            'global_price_euros' => $budget,
            'booked_euros' => $assigned,
            'real_value_euros' => $sap,
            'global_price' => $budget * 2,
            'booked' => $assigned * 2,
            'real_value' => $sap * 2,
        ]);
    }
}
