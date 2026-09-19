<?php

namespace Tests\Feature;

use App\Exports\ProjectDataExport;
use App\Livewire\Tools\ExcelFilter;
use App\Models\Company;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use App\Services\Tools\ExcelFilterService;
use App\Services\Tools\SapDataImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SapDataImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_replaces_all_rows_with_the_same_sap_order_without_accumulating(): void
    {
        [$user, $project] = $this->setupProject();
        $manual = Data::create(['project_id' => $project->id, 'real_value' => 40]);
        $oldYear = Data::create(['project_id' => $project->id, 'sap_order' => '000123', 'order_year' => 2025]);
        $manualSameOrder = Data::create(['project_id' => $project->id, 'sap_order' => ' 000123 ', 'real_value' => 75]);
        $anotherOrder = Data::create(['project_id' => $project->id, 'sap_order' => 'OTHER', 'real_value' => 90]);
        $other = $project->replicate();
        $other->pda_code = 'SAP-OTHER';
        $other->slug = 'sap-other';
        $other->save();
        $otherRow = Data::create(['project_id' => $other->id, 'sap_order' => '000123', 'order_year' => 2026]);
        $token = $this->workbook($user);
        $service = app(SapDataImporter::class);
        $this->assertSame(2, $service->import($user, $project, $token));
        $this->assertModelMissing($oldYear);
        $this->assertModelMissing($manualSameOrder);
        $this->assertSame(2, Data::where('project_id', $project->id)->where('sap_order', '000123')->count());
        $first = Data::where('project_id', $project->id)->where('order_year', 2026)->sole();
        $this->assertSame('000123', $first->sap_order);
        $this->assertSame('Pedido A', $first->description);
        $this->assertSame('Materiales', $first->supplier);
        $this->assertSame('12.50', $first->qty);
        $this->assertSame('1234.50', $first->real_value);
        $this->assertSame('617.25', $first->real_value_euros);
        $this->assertSame('2026-02-03', $first->accounting_date);
        $this->assertSame('2026-02-02', $first->document_date);
        $export = (new ProjectDataExport)->download($project, collect([$first]), ['sap_order', 'real_value', 'accounting_date']);
        $exportPath = $export->getFile()->getPathname();
        try {
            $book = IOFactory::load($exportPath);
            $sheet = $book->getActiveSheet();
            $this->assertSame('000123', $sheet->getCell('A2')->getValue());
            $this->assertSame('s', $sheet->getCell('A2')->getDataType());
            $this->assertSame('n', $sheet->getCell('B2')->getDataType());
            $this->assertSame('2026-02-03', $sheet->getCell('C2')->getFormattedValue());
            $book->disconnectWorksheets();
        } finally {
            @unlink($exportPath);
        }
        $this->assertSame(2, $service->import($user, $project, $token));
        $this->assertModelMissing($first);
        $this->assertModelExists($manual);
        $this->assertModelMissing($oldYear);
        $this->assertModelExists($otherRow);
        $this->assertModelExists($anotherOrder);
        $this->assertSame(2, Data::where('project_id', $project->id)->where('sap_order', '000123')->count());
        $this->assertSame(1, Data::where('project_id', $project->id)->where('order_year', 2026)->count());
        $this->assertSame(1, Data::where('project_id', $project->id)->where('order_year', 2025)->count());
    }

    public function test_invalid_data_or_no_matches_preserves_existing_rows(): void
    {
        [$user, $project] = $this->setupProject();
        $existing = Data::create(['project_id' => $project->id, 'sap_order' => '000123', 'order_year' => 2026]);
        foreach ([['invalid', '000123'], ['1', 'NOT-IN-EXCEL']] as [$value, $order]) {
            $project->update(['sap_order' => $order]);
            try {
                app(SapDataImporter::class)->import($user, $project, $this->workbook($user, $value));
                $this->fail('Expected validation failure');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
            $this->assertModelExists($existing);
        }
    }

    public function test_tools_preview_and_import_and_company_authorization(): void
    {
        [$user, $project] = $this->setupProject();
        $token = $this->workbook($user);
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('sourceToken', $token)->set('sapMapping', app(SapDataImporter::class)->mapping($user, $token))
            ->call('confirmSapMapping')->assertHasNoErrors()
            ->assertSet('matchedProjects.0.id', $project->id)->assertSet('matchedProjects.0.count', 2)
            ->set('selectedProjects', [$project->id])
            ->call('importSap')->assertHasNoErrors()->assertSee(__('sap.success', ['count' => 2, 'projects' => 1]));
        $outsider = User::factory()->create(['is_active' => true]);
        Livewire::actingAs($outsider)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('sourceToken', $token)->call('importSap')->assertForbidden();
    }

    public function test_custom_mapping_is_saved_reused_and_missing_columns_require_editing(): void
    {
        [$user, $project] = $this->setupProject();
        $headers = ['Pedido', 'Orden SAP', 'Concepto', 'Importe', 'Contabilizado', 'Documento', 'Unidades'];
        $token = $this->workbook($user, value: '1234.50', headers: $headers);
        $mapping = array_combine(array_values(SapDataImporter::COLUMNS), $headers);
        $upload = fn ($token) => UploadedFile::fake()->createWithContent('sap.xlsx', Storage::disk('local')->get("tools/excel-filter/sources/{$user->id}/{$token}.xlsx"));
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('upload', $upload($token))->call('analyze')->assertHasNoErrors()
            ->set('sapMapping', $mapping)
            ->assertSee(__('sap.target'))->assertSee(__('sap.sap_order'))->assertSee(__('sap.real_value'))
            ->call('confirmSapMapping')->assertHasNoErrors()
            ->assertSet('matchedProjects.0.id', $project->id)
            ->set('selectedProjects', [$project->id])->call('importSap')->assertHasNoErrors();
        $this->assertSame($mapping, $user->preferences()->where('key', 'tools.sap-import.mapping.v2')->sole()->value);
        $this->assertSame('1234.50', Data::where('project_id', $project->id)->where('order_year', 2026)->sole()->real_value);
        $this->assertSame('Pedido A', Data::where('project_id', $project->id)->where('order_year', 2026)->sole()->description);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('data', 'order_text'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('data', 'denomination'));
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('upload', $upload($token))->call('analyze')
            ->assertSet('hasSavedMapping', true)->assertSet('mappingConfirmed', false)
            ->assertSee(__('sap.saved'))
            ->call('confirmSapMapping')->assertHasNoErrors()->assertSet('mappingConfirmed', true)
            ->set('selectedProjects', [$project->id])->call('editSapMapping')
            ->assertSet('selectedProjects', [])->assertSet('matchedProjects', [])
            ->set('sapMapping.sap_order', 'Pedido')->call('confirmSapMapping')->assertHasErrors('sapMapping');
        $standard = $this->workbook($user);
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('upload', $upload($standard))->call('analyze')
            ->assertSee(__('sap.missing'))
            ->call('confirmSapMapping')->assertHasErrors('sapMapping.description');
        $outsider = User::factory()->create(['is_active' => true]);
        Livewire::actingAs($outsider)->test(ExcelFilter::class)
            ->set('upload', $upload($standard))->call('analyze')->assertSet('hasSavedMapping', false);
    }

    public function test_quantity_mapping_is_required_and_sap_order_is_not_editable(): void
    {
        [$user, $project] = $this->setupProject();
        $token = $this->workbook($user);
        $mapping = app(SapDataImporter::class)->mapping($user, $token);
        unset($mapping['qty']);
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('sourceToken', $token)->set('sapMapping', $mapping)
            ->call('confirmSapMapping')->assertHasErrors('sapMapping.qty');
        $this->assertArrayNotHasKey('sap_order', \App\Support\Data\DataTableDefinition::COLUMN_OPTIONS);
        $validator = validator(['editData' => ['sap_order' => 'tampered']], \App\Validation\DataUpdateValidation::rules());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('editData.sap_order'));
    }

    public function test_invalid_quantity_preserves_existing_imported_data(): void
    {
        [$user, $project] = $this->setupProject();
        $existing = Data::create(['project_id' => $project->id, 'sap_order' => '000123', 'qty' => 10]);
        foreach (['invalid', '', '100000000'] as $quantity) {
            try {
                app(SapDataImporter::class)->import($user, $project, $this->workbook($user, quantity: $quantity));
                $this->fail('Invalid quantity accepted');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
            $this->assertModelExists($existing);
            $this->assertSame('10.00', $existing->fresh()->qty);
        }
    }

    public function test_cleanup_preserves_imported_values_and_existing_fields(): void
    {
        [$user, $project] = $this->setupProject();
        $migration = require database_path('migrations/2026_09_19_130000_remove_duplicate_sap_data_fields.php');
        $migration->down();
        $row = Data::create(['project_id' => $project->id, 'qty' => 3, 'real_value' => 50, 'sap_order' => '000123']);
        \Illuminate\Support\Facades\DB::table('data')->where('id', $row->id)
            ->update(['order_text' => 'Imported description', 'denomination' => 'Imported supplier']);
        $migration->up();
        $this->assertSame('Imported description', $row->fresh()->description);
        $this->assertSame('Imported supplier', $row->fresh()->supplier);
        $this->assertSame('3.00', $row->fresh()->qty);
        $this->assertSame('50.00', $row->fresh()->real_value);
        $this->assertSame('000123', $row->fresh()->sap_order);
        foreach (['order_text', 'denomination', 'sap_imported'] as $field) {
            $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('data', $field));
        }
    }

    public function test_matches_filter_only_plant_and_only_selected_projects_are_imported(): void
    {
        [$user, $project] = $this->setupProject();
        $second = $project->replicate();
        $second->fill(['pda_code' => 'SECOND', 'slug' => 'second', 'sap_order' => '999', 'name' => 'Second']);
        $second->save();
        $elsewhere = $project->replicate();
        $elsewhere->fill(['pda_code' => 'ELSEWHERE', 'slug' => 'elsewhere', 'company_id' => Company::where('id', '<>', $project->company_id)->value('id')]);
        $elsewhere->save();
        $token = $this->workbook($user);
        $service = app(SapDataImporter::class);
        $mapping = $service->mapping($user, $token);
        $matches = $service->matches($user, $project->company_id, $token, $mapping);
        $this->assertEqualsCanonicalizing([$project->id, $second->id], array_column($matches, 'id'));
        $this->assertSame(2, collect($matches)->firstWhere('id', $project->id)['count']);
        $this->assertSame(1, $service->importSelected($user, $project->company_id, $token, $mapping, [$second->id]));
        $this->assertSame(0, Data::where('project_id', $project->id)->count());
        $this->assertSame(0, Data::where('project_id', $elsewhere->id)->count());
        $this->assertSame(3, $service->importSelected($user, $project->company_id, $token, $mapping, [$project->id, $second->id]));
        $this->assertSame(3, Data::whereIn('project_id', [$project->id, $second->id])->count());
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('sourceToken', $token)->set('sapMapping', $mapping)->call('confirmSapMapping')
            ->set('selectedProjects', [$project->id])->set('importPlant', '')
            ->assertHasErrors('importPlant')
            ->assertSet('selectedProjects', [])->assertSet('matchedProjects', []);
        Livewire::actingAs($user)->test(ExcelFilter::class)
            ->set('importPlant', (string) $project->company_id)
            ->set('sourceToken', $token)->set('sapMapping', $mapping)->call('confirmSapMapping')
            ->set('selectedProjects', [$elsewhere->id])->call('importSap')->assertForbidden();
    }

    public function test_multi_project_import_rolls_back_all_projects_if_one_is_invalid(): void
    {
        [$user, $project] = $this->setupProject();
        $second = $project->replicate();
        $second->fill(['pda_code' => 'SECOND', 'slug' => 'second', 'sap_order' => '999', 'rate' => 0]);
        $second->save();
        $existing = Data::create(['project_id' => $project->id, 'sap_order' => '000123', 'order_year' => 2026, 'real_value' => 50]);
        $token = $this->workbook($user);
        $service = app(SapDataImporter::class);
        try {
            $service->importSelected($user, $project->company_id, $token, $service->mapping($user, $token), [$project->id, $second->id]);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
        $this->assertModelExists($existing);
        $this->assertSame('50.00', $existing->fresh()->real_value);
        $this->assertSame(0, Data::where('project_id', $second->id)->count());
    }

    public function test_data_header_counts_and_deletion_are_scoped_to_project_sap_rows(): void
    {
        [$user, $project] = $this->setupProject();
        $sap = Data::create(['project_id' => $project->id, 'sap_order' => '000123']);
        $zero = Data::create(['project_id' => $project->id, 'sap_order' => '0']);
        $manualRows = collect([null, '', '   '])->map(fn ($order) => Data::create([
            'project_id' => $project->id, 'sap_order' => $order,
        ]));
        $other = $project->replicate();
        $other->fill(['pda_code' => 'KEEP-SAP', 'slug' => 'keep-sap']);
        $other->save();
        $otherRow = Data::create(['project_id' => $other->id, 'sap_order' => '000123']);

        Livewire::actingAs($user)->test(\App\Livewire\Data\DataTable::class, ['project' => $project])
            ->assertViewHas('totalRecordCount', 5)
            ->assertViewHas('sapRecordCount', 2)
            ->assertViewHas('manualRecordCount', 3)
            ->assertSee('(SAP 2) (Data 3)')
            ->set('search', 'no matching rows')
            ->call('deleteSapData')->assertHasNoErrors()
            ->assertViewHas('totalRecordCount', 3)
            ->assertViewHas('sapRecordCount', 0)
            ->assertViewHas('manualRecordCount', 3);

        $this->assertModelMissing($sap);
        $this->assertModelMissing($zero);
        $this->assertModelExists($otherRow);
        $manualRows->each(fn ($row) => $this->assertModelExists($row));
        $this->assertTrue((bool) $project->fresh()->data_uploaded);
    }

    public function test_deleting_last_sap_rows_clears_uploaded_flag(): void
    {
        [$user, $project] = $this->setupProject();
        $project->update(['data_uploaded' => true]);
        Data::create(['project_id' => $project->id, 'sap_order' => '000123']);
        Livewire::actingAs($user)->test(\App\Livewire\Data\DataTable::class, ['project' => $project])
            ->call('deleteSapData')->assertHasNoErrors()
            ->assertViewHas('totalRecordCount', 0);
        $this->assertFalse((bool) $project->fresh()->data_uploaded);
    }

    public function test_sap_deletion_requires_delete_permission(): void
    {
        [$user, $project] = $this->setupProject();
        $row = Data::create(['project_id' => $project->id, 'sap_order' => '000123']);
        $role = $user->roles()->where('company_id', $project->company_id)->firstOrFail();
        $role->revokePermissionTo(\App\Enums\ProjectPermissionEnum::Delete->value);
        Livewire::actingAs($user)->test(\App\Livewire\Data\DataTable::class, ['project' => $project])
            ->assertDontSee(__('sap.delete_records'))
            ->call('deleteSapData')->assertForbidden();
        $this->assertModelExists($row);
    }

    private function setupProject(): array
    {
        Storage::fake('local');
        $this->seed();
        $user = User::where('email', 'test@example.com')->sole();
        $project = Project::create([
            'company_id' => Company::where('company_code', 'CIESA')->value('id'),
            'created_by' => $user->id, 'name' => 'SAP Test', 'pda_code' => 'SAP-TEST', 'sap_order' => '000123',
            'forecast_start_date' => '2026-01-01', 'forecast_end_date' => '2027-12-31', 'rate' => 2,
            'state' => 'Planning', 'investments' => 'Innovation', 'justification' => 'Normal Capex',
            'classification_of_investments' => 'Buildings',
        ]);

        return [$user, $project];
    }

    public function test_102407_previews_and_imports_all_eleven_rows_without_a_year_filter(): void
    {
        [$user, $project] = $this->setupProject();
        $project->update(['sap_order' => '102407']);
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([['Texto de pedido', 'Orden', 'Denominación', 'Valor/mon.inf.', 'Fe.contab.', 'Fecha doc.', 'Cantidad']]);
        for ($i = 0; $i < 11; $i++) {
            $date = $i < 7 ? '23/12/2025' : '25/06/2026';
            $sheet->fromArray([['Item '.$i, '102407', $i < 3 ? 'Detail' : '', 100, $date, $date, 2]], null, 'A'.($i + 2));
        }
        $path = tempnam(sys_get_temp_dir(), 'sap');
        try {
            (new Xlsx($book))->save($path);
            $upload = UploadedFile::fake()->createWithContent('102407.xlsx', file_get_contents($path));
            $mapping = array_combine(array_values(SapDataImporter::COLUMNS), ['Texto de pedido', 'Orden', 'Denominación', 'Valor/mon.inf.', 'Fe.contab.', 'Fecha doc.', 'Cantidad']);
            Livewire::actingAs($user)->test(ExcelFilter::class)
                ->set('importPlant', (string) $project->company_id)
                ->set('upload', $upload)->call('analyze')->set('sapMapping', $mapping)->call('confirmSapMapping')
                ->assertHasNoErrors()->assertSet('matchedProjects.0.count', 11)
                ->assertDontSee('wire:model.live.debounce.500ms="importYear"', false)
                ->call('previewSapProject', $project->id)->assertHasNoErrors()
                ->assertSet('sapPreviewProject.count', 11)
                ->assertSet('sapPreviewRows', fn ($rows) => count($rows) === 11)
                ->set('selectedProjects', [$project->id])->call('importSap')->assertHasNoErrors();
            $this->assertSame(7, Data::where('project_id', $project->id)->where('order_year', 2025)->count());
            $this->assertSame(4, Data::where('project_id', $project->id)->where('order_year', 2026)->count());
        } finally {
            $book->disconnectWorksheets();
            @unlink($path);
        }
    }

    public function test_tools_respects_all_supported_languages_in_navigation_mapping_and_errors(): void
    {
        [$user, $project] = $this->setupProject();
        $token = $this->workbook($user);
        foreach (['en' => 'Import Excel into Data', 'es' => 'Importar Excel a Data', 'it' => 'Importa Excel in Data'] as $locale => $title) {
            $user->preferences()->updateOrCreate(['key' => 'locale'], ['value' => ['locale' => $locale]]);
            $this->actingAs($user)->get('/tools')->assertOk()->assertSee($title);
            $component = Livewire::actingAs($user)->test(ExcelFilter::class)
                ->set('sourceToken', $token)->set('headers', app(ExcelFilterService::class)->analyze($user->id, $token)['headers'])
                ->assertSee($title)->assertSee(__('sap.mapping', [], $locale))
                ->call('confirmSapMapping')->assertHasErrors('sapMapping.description')
                ->assertSee(__('sap.error_column', [], $locale));
            if ($locale !== 'es') {
                $component->assertDontSee('Seleccionar planta')->assertDontSee('Correspondencia de columnas');
            }
            $translations = require lang_path($locale.'/sap.php');
            $this->assertSame(array_keys(require lang_path('en/sap.php')), array_keys($translations));
        }
    }

    private function workbook(User $user, string $value = '1.234,50', ?array $headers = null, string $quantity = '12.50'): string
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([
            $headers ?? ['Texto Pedido', 'Sap Orden', 'Denominación', 'Real value', 'Fecha Contable', 'Fecha Documento', 'Quantity'],
            ['Pedido A', '000123', 'Materiales', $value, '03/02/2026', '02/02/2026', $quantity],
            ['Otro SAP', '999', 'Ignorar', 12, '03/02/2026', '02/02/2026', 3],
            ['Otro año', '000123', 'Ignorar', 12, '03/02/2025', '02/02/2025', 4],
        ]);
        $sheet->setCellValue('F2', Date::PHPToExcel(new \DateTime('2026-02-02')));
        $sheet->getStyle('F2')->getNumberFormat()->setFormatCode('m/d/yy');
        $sheet->getStyle('D2')->getNumberFormat()->setFormatCode('"$"#,##0.00');
        $path = tempnam(sys_get_temp_dir(), 'sap');
        try {
            (new Xlsx($book))->save($path);

            return app(ExcelFilterService::class)->store(new UploadedFile($path, 'sap.xlsx', null, null, true), $user->id);
        } finally {
            $book->disconnectWorksheets();
            @unlink($path);
        }
    }
}
