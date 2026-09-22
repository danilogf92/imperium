<?php

namespace Tests\Feature;

use App\Exports\ProjectDataImportExport;
use App\Models\Company;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectDataExcelImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDataImportRoundTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_can_be_imported_into_another_project_with_zero_values_and_literal_text(): void
    {
        $this->seed();
        $source = Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => User::where('email', 'test@example.com')->value('id'),
            'name' => 'Export source', 'pda_code' => 'EXPORT-SOURCE',
            'forecast_start_date' => '2026-01-01', 'forecast_end_date' => '2027-12-31',
            'rate' => 2, 'state' => 'Planning', 'investments' => 'Innovation',
            'justification' => 'Normal Capex', 'classification_of_investments' => 'Buildings',
        ]);
        $target = $source->replicate();
        $target->name = 'Import destination';
        $target->pda_code = 'IMPORT-TARGET';
        $target->slug = 'import-destination';
        $target->save();

        foreach ([0, 1250.75] as $amount) {
            Data::create([
                'project_id' => $source->id, 'area' => 'Security',
                'description' => '=CCTV installation', 'qty' => 0,
                'unit_price' => $amount, 'global_price' => $amount,
                'code' => '00123', 'order_no' => '000456',
                'observations' => '=Keep this literal text',
            ]);
        }

        foreach ([null, '   '] as $description) {
            Data::create([
                'project_id' => $source->id, 'area' => null,
                'description' => $description, 'qty' => 1,
                'unit_price' => 50, 'global_price' => 50,
            ]);
        }

        $response = (new ProjectDataImportExport)->download($source, $source->data()->orderBy('id')->get());
        $path = $response->getFile()->getPathname();

        try {
            $this->assertSame(4, app(ProjectDataExcelImporter::class)->import($target, $path));
            $rows = $target->data()->orderBy('id')->get();
            $this->assertCount(4, $rows);
            foreach ($rows->take(2) as $row) {
                $this->assertSame('0.00', $row->qty);
                $this->assertSame('=CCTV installation', $row->description);
                $this->assertSame('00123', $row->code);
                $this->assertSame('000456', $row->order_no);
                $this->assertSame('=Keep this literal text', $row->observations);
            }
            $this->assertSame('0.00', $rows[0]->unit_price);
            $this->assertSame('0.00', $rows[0]->global_price);
            $this->assertSame('1250.75', $rows[1]->global_price);
            foreach ($rows->skip(2) as $row) {
                $this->assertNull($row->description);
                $this->assertNull($row->area);
                $this->assertSame('50.00', $row->global_price);
            }
            $this->assertTrue($target->refresh()->data_uploaded);
            $this->assertSame(4, $source->data()->count());
        } finally {
            @unlink($path);
        }
    }
}
