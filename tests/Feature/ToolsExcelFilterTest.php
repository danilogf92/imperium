<?php

namespace Tests\Feature;

use App\Livewire\Tools\ExcelFilter;
use App\Models\User;
use App\Services\Tools\ExcelFilterService;
use App\Services\Tools\ExcelFilterValueConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ToolsExcelFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_filter_preserves_text_codes_and_exports_the_preview_columns_and_rows(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $upload = $this->workbookUpload();
        $service = app(ExcelFilterService::class);
        $token = $service->store($upload, $user->id);

        $analysis = $service->analyze($user->id, $token);
        $this->assertSame(['Orden', 'Texto', 'Monto'], $analysis['headers']);
        $this->assertSame(3, $analysis['columnCount']);
        $this->assertCount(5, $analysis['sampleRows']);

        $firstPage = $service->preview($user->id, $token, [0, 1], 0, '000123', 1);
        $this->assertSame(31, $firstPage['count']);
        $this->assertCount(25, $firstPage['rows']);
        $this->assertSame(['Orden', 'Texto'], $firstPage['headers']);
        $this->assertSame('000123', $firstPage['rows'][0][0]);

        $secondPage = $service->preview($user->id, $token, [0, 1], 0, '000123', 2);
        $this->assertCount(6, $secondPage['rows']);
        $this->assertSame(2, $secondPage['lastPage']);

        $exportPath = $service->export($user->id, $token, [0, 1], 0, '000123');
        try {
            $book = IOFactory::load($exportPath);
            $this->assertCount(1, $book->getAllSheets());
            $sheet = $book->getActiveSheet();
            $this->assertSame('B', $sheet->getHighestColumn());
            $this->assertSame(32, $sheet->getHighestDataRow());
            $this->assertSame('Orden', $sheet->getCell('A1')->getFormattedValue());
            $this->assertSame('000123', $sheet->getCell('A2')->getFormattedValue());
            $this->assertSame('A2', $sheet->getFreezePane());
            $this->assertSame('A1:B32', $sheet->getAutoFilter()->getRange());
            $book->disconnectWorksheets();
        } finally {
            unlink($exportPath);
        }
    }

    public function test_livewire_can_modify_and_preview_again_without_reuploading(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['is_active' => true]);
        $upload = $this->workbookUpload();

        $this->get('/tools')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/tools')->assertOk()->assertSee('Herramientas');
        $component = Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $upload)
            ->call('analyze')
            ->assertHasNoErrors()
            ->assertSet('headers', ['Orden', 'Texto', 'Monto'])
            ->call('selectNone')
            ->assertSet('selectedColumns', [])
            ->set('filterColumn', '0')
            ->set('projectCode', '000123')
            ->call('preview')
            ->assertHasErrors('selectedColumns')
            ->call('selectAll')
            ->set('selectedColumns', [0, 1])
            ->call('preview')
            ->assertHasNoErrors()
            ->assertSet('matchCount', 31)
            ->assertSee('Descargar Excel')
            ->call('goToPage', 2)
            ->assertSet('previewPage', 2)
            ->call('modify')
            ->assertSet('previewReady', false)
            ->assertSet('projectCode', '000123')
            ->set('projectCode', 'SEAF-26-01')
            ->call('preview')
            ->assertSet('matchCount', 10)
            ->assertSet('previewPage', 1)
            ->call('download')
            ->assertFileDownloaded('filtered-seaf-26-01.xlsx');

        $component->call('modify')->set('projectCode', 'missing')->call('preview')
            ->assertSet('matchCount', 0)
            ->assertDontSee('Descargar Excel');
    }

    public function test_column_types_create_real_numeric_cells_and_blue_headers(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $service = app(ExcelFilterService::class);
        $token = $service->store($this->workbookUpload(), $user->id);
        $types = [0 => 'text', 2 => 'number'];

        $preview = $service->preview($user->id, $token, [0, 2], 0, '000123', 1, columnTypes: $types);
        $this->assertSame(['000123', '365.50'], $preview['rows'][0]);

        $path = $service->export($user->id, $token, [0, 2], 0, '000123', $types);
        try {
            $book = IOFactory::load($path);
            $sheet = $book->getActiveSheet();
            $this->assertSame(DataType::TYPE_INLINE, $sheet->getCell('A2')->getDataType());
            $this->assertSame('000123', $sheet->getCell('A2')->getFormattedValue());
            $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('B2')->getDataType());
            $this->assertSame(365.5, $sheet->getCell('B2')->getValue());
            $this->assertSame('365.50', $sheet->getCell('B2')->getFormattedValue());
            $this->assertSame('FF2563EB', $sheet->getStyle('A1')->getFill()->getStartColor()->getARGB());
            $this->assertSame('FFFFFFFF', $sheet->getStyle('A1')->getFont()->getColor()->getARGB());
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }

        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $this->workbookUpload())->call('analyze')
            ->assertSet('columnTypes', ['text', 'text', 'text'])
            ->set('selectedColumns', [0, 1])
            ->set('columnTypes.1', 'number')
            ->set('filterColumn', '0')
            ->set('projectCode', '000123')
            ->call('preview')
            ->assertHasErrors('columnTypes');
    }

    public function test_number_conversion_handles_grouping_and_preserves_text_codes(): void
    {
        $converter = new ExcelFilterValueConverter;
        $this->assertSame('3,364.75', $converter->convert('3,364.75', 'number', 'Monto')['display']);
        $this->assertSame('3364.75', $converter->convert('3.364,75', 'number', 'Monto')['value']);
        $this->assertSame('000123', $converter->convert('000123', 'text', 'Orden')['display']);
        $this->assertSame('123', $converter->convert('000123', 'number', 'Orden')['display']);
    }

    public function test_invalid_and_empty_header_workbooks_are_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', UploadedFile::fake()->createWithContent('bad.xlsx', 'not an Excel file'))
            ->call('analyze')
            ->assertHasErrors('upload');

        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray(['Orden', 'Orden'], null, 'A1');
        $sheet->fromArray(['123', 'value'], null, 'A2');
        $upload = $this->uploadFromSpreadsheet($book, 'duplicate.xlsx');
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $upload)->call('analyze')->assertHasErrors('upload');

        $headerOnly = new Spreadsheet;
        $headerOnly->getActiveSheet()->setCellValue('A1', 'Orden');
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $this->uploadFromSpreadsheet($headerOnly, 'empty.xlsx'))
            ->call('analyze')->assertHasErrors('upload');

        $twoSheets = new Spreadsheet;
        $twoSheets->getActiveSheet()->fromArray([['Orden'], ['A-01']], null, 'A1');
        $twoSheets->createSheet()->setCellValue('A1', 'Other');
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $this->uploadFromSpreadsheet($twoSheets, 'two-sheets.xlsx'))
            ->call('analyze')->assertHasErrors('upload');
    }

    public function test_xls_workbooks_are_accepted(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $book = new Spreadsheet;
        $book->getActiveSheet()->fromArray([['Orden', 'Texto'], ['A-01', 'row']], null, 'A1');
        $upload = $this->uploadFromSpreadsheet($book, 'source.xls');
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $upload)->call('analyze')
            ->assertHasNoErrors()
            ->assertSet('headers', ['Orden', 'Texto']);
    }

    public function test_temporary_workbooks_are_private_to_the_uploader(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $service = app(ExcelFilterService::class);
        $token = $service->store($this->workbookUpload(), $owner->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->analyze($other->id, $token);
    }

    private function workbookUpload(): UploadedFile
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray(['Orden', 'Texto', 'Monto'], null, 'A1');
        for ($row = 2; $row <= 31; $row++) {
            $sheet->setCellValueExplicit("A{$row}", '000123', DataType::TYPE_STRING);
            $sheet->setCellValue("B{$row}", "Matching row {$row}");
            $sheet->setCellValue("C{$row}", $row * 10);
        }
        $sheet->setCellValue('C2', 365.5);
        $sheet->getStyle('C2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('A32', 123);
        $sheet->getStyle('A32')->getNumberFormat()->setFormatCode('000000');
        $sheet->setCellValue('B32', 'Formatted code');
        for ($row = 33; $row <= 42; $row++) {
            $sheet->setCellValueExplicit("A{$row}", 'SEAF-26-01', DataType::TYPE_STRING);
            $sheet->setCellValue("B{$row}", "Other row {$row}");
        }

        return $this->uploadFromSpreadsheet($book, 'source.xlsx');
    }

    private function uploadFromSpreadsheet(Spreadsheet $book, string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'tools-test-');
        $writer = str_ends_with($name, '.xls') ? new Xls($book) : new Xlsx($book);
        $writer->save($path);
        $book->disconnectWorksheets();
        $upload = UploadedFile::fake()->createWithContent($name, file_get_contents($path));
        unlink($path);

        return $upload;
    }
}
