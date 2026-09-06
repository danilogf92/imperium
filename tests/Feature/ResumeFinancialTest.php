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
                        && $chart['colors'][7] === '#F97316'
                        && $bars[8] === 300.0 * $rate
                        && $chart['colors'][8] === '#7DD3FC'
                        && $chart['colors'][9] === '#7DD3FC'
                        && $chart['chart']['type'] === 'bar'
                        && $chart['chart']['height'] === 300
                        && ! isset($chart['annotations'])
                        && ! isset($chart['yaxis']['max'])
                        && $chart['yaxis']['forceNiceScale'] === true;
                });
        }

        $component->set('yearFilter', [])
            ->assertViewHas('cashFlowSummary', fn ($summary) => $summary['total'] === 2000.0 && $summary['outside_total'] === 0.0)
            ->assertViewHas('cashFlowChartOptions', fn ($chart) => count($chart['series'][0]['data']) === 24);

        ProjectMilestone::where('cycle_year', 2026)->delete();
        $component->set('yearFilter', ['2026'])
            ->assertViewHas('cashFlowSummary', fn ($summary) => $summary['total'] === 0.0 && $summary['outside_total'] === 1000.0)
            ->assertViewHas('cashFlowChartOptions', fn ($chart) => count($chart['series'][0]['data']) === 12)
            ->assertSee('Outside selected years (2027)');
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
