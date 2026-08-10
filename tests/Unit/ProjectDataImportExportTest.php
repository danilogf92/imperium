<?php

namespace Tests\Unit;

use App\Exports\ProjectDataImportExport;
use App\Models\Data;
use App\Models\Project;
use App\Services\ProjectDataTemplateGenerator;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProjectDataImportExportTest extends TestCase
{
    public function test_it_exports_every_import_template_column_in_the_expected_order(): void
    {
        $project = (new Project())->forceFill(['id' => 15, 'name' => 'Import Ready']);
        $row = (new Data())->forceFill([
            'id' => 99,
            'area' => 'Safety',
            'description' => 'Guard installation',
            'qty' => 2,
            'unit_price' => 125.5,
            'global_price' => 251,
        ]);

        $response = (new ProjectDataImportExport())->download($project, new Collection([$row]));
        $path = $response->getFile()->getPathname();

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getSheet(0);

            $this->assertSame(ProjectDataTemplateGenerator::HEADERS, $sheet->rangeToArray('A1:V1')[0]);
            $this->assertSame('Safety', $sheet->getCell('B2')->getValue());
            $this->assertSame('Guard installation', $sheet->getCell('E2')->getValue());
            $this->assertSame(2.0, $sheet->getCell('J2')->getValue());

            $spreadsheet->disconnectWorksheets();
        } finally {
            @unlink($path);
        }
    }
}
