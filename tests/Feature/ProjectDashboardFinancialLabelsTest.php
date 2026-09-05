<?php

namespace Tests\Feature;

use App\Exports\ProjectDetailDashboardExport;
use App\Livewire\Project\DashboardProjects;
use App\Models\Company;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProjectDashboardFinancialLabelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_committed_and_financial_names_match_dashboard_and_excel_in_both_currencies(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $project = Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id,
            'name' => 'Security office relocation',
            'pda_code' => 'SECURITY-TEST',
            'rate' => 2,
            'state' => 'Execution',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
            'forecast_start_date' => '2026-01-01',
            'forecast_end_date' => '2027-12-31',
        ]);
        foreach ([[80, 30], [10, 20]] as [$assigned, $sap]) {
            Data::create([
                'project_id' => $project->id,
                'area' => 'Security',
                'global_price_euros' => 100,
                'booked_euros' => $assigned,
                'real_value_euros' => $sap,
            ]);
        }

        $component = Livewire::actingAs($user)->test(DashboardProjects::class, ['project' => $project]);
        foreach (['euro' => [1, '€', 'booked_euros', 'Assigned'], 'dollar' => [2, '$', 'real_value_euros', 'Booked (Real SAP)']] as $currency => [$rate, $symbol, $valueColumn, $groupLabel]) {
            $component->set('dollarOrEuro', $currency)
                ->set('investments', $valueColumn)
                ->assertSee('Assigned')
                ->assertSee('Booked (Real SAP)')
                ->assertSee('Committed');
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
            $xpath = new \DOMXPath($document);
            $card = $xpath->query('//article[.//p[normalize-space(.)="Committed"]]')->item(0);
            $this->assertNotNull($card);
            $this->assertStringContainsString($symbol.' '.number_format(40 * $rate, 2), $card->textContent);

            $response = (new ProjectDetailDashboardExport)->download($project, 'area', $valueColumn, $currency, $rate);
            $path = $response->getFile()->getPathname();
            try {
                $workbook = IOFactory::load($path);
                $summary = $workbook->getSheetByName('Summary');
                $this->assertSame('Assigned', $summary->getCell('D7')->getValue());
                $this->assertSame('Booked (Real SAP)', $summary->getCell('D8')->getValue());
                $this->assertSame('Committed', $summary->getCell('D9')->getValue());
                $this->assertEquals(90 * $rate, $summary->getCell('E7')->getValue());
                $this->assertEquals(50 * $rate, $summary->getCell('E8')->getValue());
                $this->assertEquals(40 * $rate, $summary->getCell('E9')->getValue());
                $this->assertSame($groupLabel.' ('.$symbol.')', $workbook->getSheetByName('Grouping')->getCell('B1')->getValue());
                $detail = $workbook->getSheetByName('Project Data');
                $this->assertSame('Assigned', $detail->getCell('L1')->getValue());
                $this->assertSame('Booked (Real SAP)', $detail->getCell('M1')->getValue());
                $this->assertSame('Committed', $detail->getCell('N1')->getValue());
                $this->assertEquals(50 * $rate, $detail->getCell('N2')->getValue());
                $this->assertEquals(-10 * $rate, $detail->getCell('N3')->getValue());
                $this->assertSame('A1:N3', $detail->getAutoFilter()->getRange());
                $workbook->disconnectWorksheets();
            } finally {
                unlink($path);
            }
        }
    }
}
