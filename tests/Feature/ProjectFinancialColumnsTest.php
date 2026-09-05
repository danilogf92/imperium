<?php

namespace Tests\Feature;

use App\Exports\ProjectExport;
use App\Livewire\Project\Table;
use App\Models\Company;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use App\Support\MoneyValueFormatter;
use App\Support\Project\ProjectTableDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProjectFinancialColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_headers_match_values_and_new_columns_can_be_selected_and_sorted(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $project = Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id,
            'name' => 'Financial columns test',
            'pda_code' => 'FIN-COLUMNS',
            'forecast_start_date' => '2026-01-01',
            'forecast_end_date' => '2027-12-31',
            'rate' => 1,
            'state' => 'Planning',
            'investments' => 'Innovation',
            'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
        ]);
        Data::create([
            'project_id' => $project->id,
            'executed_euros' => 123,
            'booked_euros' => 234,
            'executed_dollars' => 345,
            'booked' => 456,
        ]);

        $component = Livewire::actingAs($user)->test(Table::class, ['active' => true]);
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
        $xpath = new \DOMXPath($document);
        $headers = $xpath->query('//table[contains(@class,"project-table")]/thead/tr/th');
        $cells = $xpath->query('//table[contains(@class,"project-table")]/tbody/tr[1]/*');
        $this->assertSame($headers->length, $cells->length);

        foreach ([
            'executed_euros' => ['Executed Euros', 123, '€ '],
            'booked_euros' => ['Booked Euros', 234, '€ '],
            'executed_dollars' => ['Executed Dollars', 345, '$ '],
            'booked' => ['Booked Dollars', 456, '$ '],
        ] as $column => [$label, $amount, $symbol]) {
            $index = null;
            foreach ($headers as $position => $header) {
                if (trim($header->textContent) === $label) {
                    $index = $position;
                    break;
                }
            }
            $this->assertNotNull($index, $label);
            $this->assertSame(MoneyValueFormatter::compact($amount, $symbol), trim($cells->item($index)->textContent));
            $component->set('visibleColumns', ['name', $column, 'actions'])
                ->call('setSortBy', $column)
                ->assertSet('sortBy', $column)
                ->assertViewHas('projects', fn ($projects) => (float) $projects->first()->{$column} === (float) $amount);
        }

        $project->update(['project_idea_path' => 'project-ideas/example.pdf']);
        $allColumns = array_values(array_diff(array_keys(ProjectTableDefinition::COLUMN_OPTIONS), ['actions']));
        $this->assertExportValues($user, array_keys(ProjectTableDefinition::COLUMN_OPTIONS), $allColumns, [
            'executed_euros' => 123,
            'booked_euros' => 234,
            'executed_dollars' => 345,
            'booked' => 456,
            'project_ideas' => 'Yes',
        ]);

        $component->set('visibleColumns', ['name', 'executed_euros', 'booked', 'actions'])
            ->set('columnViewName', 'Commercial export')
            ->call('saveColumnView');
        $viewId = $component->get('selectedColumnView');
        $component->call('resetColumns')->set('selectedColumnView', $viewId);
        $selectedColumns = $component->get('visibleColumns');
        $this->assertExportValues($user, $selectedColumns, ['name', 'executed_euros', 'booked'], [
            'name' => $project->name,
            'executed_euros' => 123,
            'booked' => 456,
        ]);
    }

    private function assertExportValues(User $user, array $selected, array $expectedColumns, array $values): void
    {
        $response = (new ProjectExport)->download($user, [
            'columns' => $selected,
            'sortBy' => 'executed_euros',
            'sortDir' => 'DESC',
        ]);
        $path = $response->getFile()->getPathname();
        try {
            $workbook = IOFactory::load($path);
            $sheet = $workbook->getActiveSheet();
            $this->assertSame(count($expectedColumns), $sheet->getHighestColumn() === 'A'
                ? 1 : Coordinate::columnIndexFromString($sheet->getHighestColumn()));
            foreach ($values as $key => $expected) {
                $column = array_search($key, $expectedColumns, true) + 1;
                $this->assertSame(ProjectTableDefinition::COLUMN_OPTIONS[$key], $sheet->getCell([$column, 1])->getValue());
                $this->assertEquals($expected, $sheet->getCell([$column, 2])->getValue());
            }
            $workbook->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }
}
