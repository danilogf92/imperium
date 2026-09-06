<?php

namespace Tests\Feature;

use App\Filament\Resources\ExcelTemplates\Pages\CreateExcelTemplate;
use App\Filament\Resources\ExcelTemplates\Pages\EditExcelTemplate;
use App\Livewire\Templates\TemplateLibrary;
use App\Models\Company;
use App\Models\ExcelTemplate;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TemplateLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_general_and_plant_files_are_filtered_and_downloads_enforce_the_same_access(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('PROJECT MANAGER CIESA');
        $ciesa = Company::where('company_code', 'CIESA')->sole();
        $gralco = Company::where('company_code', 'GRALCO')->sole();
        $general = $this->file('General guide');
        $allowed = $this->file('CIESA guide', false);
        $allowed->companies()->attach($ciesa);
        $denied = $this->file('GRALCO guide', false);
        $denied->companies()->attach($gralco);
        $inactive = $this->file('Inactive guide');
        $inactive->update(['is_active' => false]);

        Livewire::actingAs($user)->test(TemplateLibrary::class)
            ->assertSee('General guide')->assertSee('CIESA guide')->assertDontSee('GRALCO guide')
            ->assertDontSee('Inactive guide')
            ->set('section', 'general')->assertSee('General guide')->assertDontSee('CIESA guide')
            ->set('section', (string) $ciesa->id)->assertSee('CIESA guide')->assertDontSee('General guide')
            ->set('section', (string) $gralco->id)->assertSet('section', 'all')->assertDontSee('GRALCO guide');

        $this->actingAs($user)->get(route('templates.download', $general))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('templates.download', $allowed))->assertOk();
        $this->get(route('templates.download', $denied))->assertNotFound();
        $this->get(route('templates.download', $inactive))->assertNotFound();

        $withoutPlant = User::factory()->create(['is_active' => true]);
        Livewire::actingAs($withoutPlant)->test(TemplateLibrary::class)
            ->assertSee('General guide')->assertDontSee('CIESA guide');
        $this->actingAs($withoutPlant)->get(route('templates.download', $general))->assertOk();
        $this->get(route('templates.download', $allowed))->assertNotFound();
    }

    public function test_admin_upload_publishes_a_file_with_plant_permissions(): void
    {
        $admin = User::factory()->create(['can_access_admin' => true, 'is_active' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $ciesa = Company::where('company_code', 'CIESA')->sole();
        Livewire::actingAs($admin)->test(CreateExcelTemplate::class)
            ->fillForm([
                'name' => 'Plant handbook',
                'category' => 'guides',
                'is_global' => false,
                'companies' => [$ciesa->id],
                'is_active' => true,
                'file_path' => UploadedFile::fake()->createWithContent('handbook.pdf', "%PDF-1.4\nTest PDF document"),
            ])->call('create')->assertHasNoFormErrors();

        $file = ExcelTemplate::where('name', 'Plant handbook')->sole();
        $this->assertFalse($file->is_global);
        $this->assertSame([$ciesa->id], $file->companies()->pluck('companies.id')->all());
        Storage::disk('local')->assertExists($file->file_path);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('PROJECT MANAGER CIESA');
        Livewire::actingAs($user)->test(TemplateLibrary::class)->assertSee('Plant handbook');
    }

    public function test_admin_can_switch_between_all_users_and_multiple_plant_permissions(): void
    {
        $admin = User::factory()->create(['can_access_admin' => true, 'is_active' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $file = $this->file('Shared handbook');
        $ciesa = Company::where('company_code', 'CIESA')->sole();
        $gralco = Company::where('company_code', 'GRALCO')->sole();

        Livewire::actingAs($admin)->test(EditExcelTemplate::class, ['record' => $file->getRouteKey()])
            ->assertDontSee('Available for users')
            ->assertFormFieldIsHidden('companies')
            ->fillForm(['is_global' => false])
            ->assertFormFieldIsVisible('companies')
            ->fillForm(['companies' => [$ciesa->id, $gralco->id]])
            ->call('save')->assertHasNoFormErrors();

        $this->assertFalse($file->refresh()->is_global);
        $this->assertCount(2, $file->companies);
        foreach (['PROJECT MANAGER CIESA', 'PROJECT MANAGER GRALCO'] as $role) {
            $user = User::factory()->create(['is_active' => true]);
            $user->assignRole($role);
            $this->actingAs($user)->get(route('templates.download', $file))->assertOk();
        }
        $withoutPlant = User::factory()->create(['is_active' => true]);
        $this->actingAs($withoutPlant)->get(route('templates.download', $file))->assertNotFound();

        Livewire::actingAs($admin)->test(EditExcelTemplate::class, ['record' => $file->getRouteKey()])
            ->fillForm(['is_global' => true])
            ->assertFormFieldIsHidden('companies')
            ->call('save')->assertHasNoFormErrors();
        $this->actingAs($withoutPlant)->get(route('templates.download', $file))->assertOk();
    }

    public function test_upload_rejects_oversized_and_unsupported_files_and_requires_a_plant(): void
    {
        $admin = User::factory()->create(['can_access_admin' => true, 'is_active' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::actingAs($admin)->test(CreateExcelTemplate::class)->fillForm([
            'name' => 'Too large', 'category' => 'guides', 'is_global' => true,
            'file_path' => UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'),
        ])->call('create')->assertHasFormErrors(['file_path']);
        Livewire::actingAs($admin)->test(CreateExcelTemplate::class)->fillForm([
            'name' => 'Unsupported', 'category' => 'guides', 'is_global' => true,
            'file_path' => UploadedFile::fake()->createWithContent('script.php', '<?php echo 1;'),
        ])->call('create')->assertHasFormErrors(['file_path']);
        Livewire::actingAs($admin)->test(CreateExcelTemplate::class)->fillForm([
            'name' => 'No plant', 'category' => 'guides', 'is_global' => false, 'companies' => [],
            'file_path' => UploadedFile::fake()->createWithContent('guide.pdf', "%PDF-1.4\nTest"),
        ])->call('create')->assertHasFormErrors(['companies']);
    }

    public function test_supported_formats_can_be_uploaded_and_downloaded_with_their_content_type(): void
    {
        $admin = User::factory()->create(['can_access_admin' => true, 'is_active' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        foreach (ExcelTemplate::FILE_TYPES as $extension => $mime) {
            Livewire::actingAs($admin)->test(CreateExcelTemplate::class)->fillForm([
                'name' => 'Format '.$extension,
                'category' => 'guides', 'is_global' => true, 'is_active' => true,
                'file_path' => UploadedFile::fake()->create('example.'.$extension, 10240, $mime),
            ])->call('create')->assertHasNoFormErrors();
            $file = ExcelTemplate::where('name', 'Format '.$extension)->sole();
            $this->actingAs($admin)->get(route('templates.download', $file))->assertOk()
                ->assertHeader('Content-Type', $mime);
        }
    }

    private function file(string $name, bool $global = true): ExcelTemplate
    {
        $path = 'excel-templates/'.uniqid().'.pdf';
        Storage::disk('local')->put($path, "%PDF-1.4\nTest");

        return ExcelTemplate::create([
            'name' => $name, 'category' => 'guides', 'file_path' => $path,
            'disk' => 'local', 'original_file_name' => 'guide.pdf', 'is_active' => true, 'is_global' => $global,
        ]);
    }
}
