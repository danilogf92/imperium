<?php

namespace Tests\Feature;

use App\Enums\ProjectStateEnum;
use App\Exports\ProjectDashboardExport;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\SetUserLocale;
use App\Livewire\Project\DashboardProjects;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_literal_translation_references_exist_in_every_supported_language(): void
    {
        $missing = [];
        foreach ([app_path(), resource_path('views')] as $directory) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                preg_match_all('/(?:__|trans_choice)\(\s*([\'"])((?:\\\\.|(?!\1).)*?)\1/s', file_get_contents($file->getPathname()), $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = $match[1] === "'" ? str_replace(["\\'", '\\\\'], ["'", '\\'], $match[2]) : stripcslashes($match[2]);
                    if (preg_match('/^[a-z_]+\.$/', $key)) {
                        continue; // Namespace prefix concatenated with a dynamic key.
                    }
                    foreach (array_keys(config('locales.supported')) as $locale) {
                        if (! app('translator')->hasForLocale($key, $locale)) {
                            $missing[] = "$locale: $key";
                        }
                    }
                }
            }
        }
        $this->assertSame([], array_values(array_unique($missing)));
    }

    public function test_supported_catalogs_have_matching_keys_and_placeholders(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach (['es', 'it'] as $locale) {
            $translated = json_decode(file_get_contents(lang_path("$locale.json")), true, flags: JSON_THROW_ON_ERROR);
            $this->assertEqualsCanonicalizing(array_keys($english), array_keys($translated), $locale);
            foreach ($english as $key => $value) {
                preg_match_all('/:[a-zA-Z_][a-zA-Z_0-9]*/', $value, $original);
                preg_match_all('/:[a-zA-Z_][a-zA-Z_0-9]*/', $translated[$key], $localized);
                $this->assertEqualsCanonicalizing(array_unique($original[0]), array_unique($localized[0]), "$locale: $key");
            }
            foreach (glob(lang_path('en/*.php')) as $file) {
                $group = basename($file);
                $source = Arr::dot(require $file);
                $target = Arr::dot(require lang_path("$locale/$group"));
                foreach ($source as $key => $value) {
                    if (str_starts_with($key, 'custom.') || $value === []) {
                        continue;
                    }
                    $this->assertArrayHasKey($key, $target, "$locale/$group: $key");
                }
            }
        }
    }

    public function test_saved_language_controls_dates_validation_and_notification_preference(): void
    {
        $user = User::factory()->create();
        foreach (['es' => 'septiembre', 'it' => 'settembre', 'en' => 'September'] as $locale => $month) {
            $user->preferences()->updateOrCreate(['key' => 'locale'], ['value' => ['locale' => $locale]]);
            $request = Request::create('/profile');
            $request->setUserResolver(fn () => $user);
            (new SetUserLocale)->handle($request, fn () => response('ok'));
            $this->assertSame($locale, app()->getLocale());
            $this->assertSame($locale, $user->preferredLocale());
            $this->assertSame($month, Carbon::create(2026, 9, 1)->translatedFormat('F'));
            $this->assertSame($month, CarbonImmutable::create(2026, 9, 1)->translatedFormat('F'));
            $message = Validator::make([], ['forecast_start_date' => 'required'])->errors()->first();
            $this->assertStringContainsString(__('validation.attributes.forecast_start_date'), $message);
            $this->assertStringNotContainsString('forecast start date', $message);
        }
        $user->preferences()->updateOrCreate(['key' => 'locale'], ['value' => ['locale' => 'unsupported']]);
        $this->assertSame('en', $user->preferredLocale());
    }

    public function test_another_users_language_does_not_leak_from_the_session(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->withSession(['locale' => 'it'])->actingAs($user)->get('/profile')->assertOk()->assertSee('lang="en"', false);
    }

    public function test_guest_authentication_respects_session_language(): void
    {
        $this->withSession(['locale' => 'es'])->get('/login')->assertOk()->assertSee('lang="es"', false);
    }

    public function test_planning_navigation_and_admin_dashboard_follow_the_users_language(): void
    {
        $user = User::factory()->create(['is_active' => true, 'can_access_admin' => true]);
        foreach (['en' => 'Planning', 'es' => 'Planificación', 'it' => 'Pianificazione'] as $locale => $planning) {
            $user->preferences()->updateOrCreate(['key' => 'locale'], ['value' => ['locale' => $locale]]);
            $this->actingAs($user)->get('/profile')->assertOk()->assertSee($planning);
            $this->assertSame($planning, __('Planification'));
            $this->actingAs($user)->get('/admin')->assertOk()->assertSee(__('Dashboard'));
            $this->assertSame(__('Dashboard'), Dashboard::getNavigationLabel());
            $this->assertSame(__('Dashboard'), (new Dashboard)->getTitle());
        }
    }

    public function test_admin_component_translations_do_not_fall_back_to_english(): void
    {
        foreach (['es' => ['Sí', 'Cargando...'], 'it' => ['Sì', 'Caricamento...']] as $locale => [$yes, $loading]) {
            $this->assertSame($yes, __('filament-tables::table.columns.icon.boolean.true', [], $locale));
            $this->assertSame($loading, __('filament-tables::table.loading', [], $locale));
        }
    }

    public function test_enum_labels_change_without_changing_stored_values_or_user_supplied_text(): void
    {
        foreach (['es', 'it'] as $locale) {
            app()->setLocale($locale);
            $this->assertSame('Planning', ProjectStateEnum::Planning->value);
            $this->assertNotSame('Planning', ProjectStateEnum::Planning->getLabel());
            $html = Blade::render('<x-dashboard-filter-dropdown label="User" model="user" :options="$options" selected="1" :show-selection="true" />', [
                'options' => [['value' => '1', 'label' => 'Planning']],
            ]);
            $this->assertStringContainsString('Planning', $html);
            $this->assertStringNotContainsString(ProjectStateEnum::Planning->getLabel(), $html);
            $chips = Blade::render('<x-filter-chips :filters="$filters" />', [
                'filters' => [['model' => 'search', 'label' => 'Search', 'value' => 'Planning']],
            ]);
            $this->assertStringContainsString('Planning', $chips);
            $statusChip = Blade::render('<x-filter-chips :filters="$filters" />', [
                'filters' => [['model' => 'state', 'label' => 'Status', 'value' => 'planning', 'translate' => true]],
            ]);
            $this->assertStringContainsString(ProjectStateEnum::Planning->getLabel(), $statusChip);
        }
    }

    public function test_chart_toolbar_and_month_names_are_localized(): void
    {
        foreach (['es', 'it', 'en'] as $locale) {
            app()->setLocale($locale);
            $this->assertIsArray(__('ui.chart'));
            $html = Blade::render('<x-chart-locale />');
            $this->assertStringContainsString('defaultLocale:', $html);
            $this->assertStringNotContainsString('ui.chart', $html);
            $this->assertStringContainsString(CarbonImmutable::create(2026, 9, 1)->locale($locale)->translatedFormat('F'), $html);
        }
    }

    public function test_export_formulas_follow_translated_sheet_names_and_statuses(): void
    {
        foreach (['es', 'it'] as $locale) {
            app()->setLocale($locale);
            $book = new Spreadsheet;
            $sheet = $book->getActiveSheet()->setTitle(__('Projects'));
            $sheet->setCellValue('D2', 'Planning'); // Literal project name.
            $sheet->setCellValue('F2', ProjectStateEnum::Execution->getLabel());
            $sheet->setCellValue('P2', 2);
            $dashboard = $book->createSheet()->setTitle(__('Dashboard'));
            $export = new ProjectDashboardExport;
            try {
                $method = new \ReflectionMethod($export, 'buildDashboardSheet');
                $method->invoke($export, $dashboard, collect());
                $this->assertSame(1, $dashboard->getCell('A5')->getCalculatedValue());
                $this->assertSame(1, $dashboard->getCell('D5')->getCalculatedValue());
                $this->assertSame(2, $dashboard->getCell('J5')->getCalculatedValue());
                $this->assertSame('Planning', $sheet->getCell('D2')->getValue());
            } finally {
                foreach ((new \ReflectionProperty($export, 'temporaryImages'))->getValue($export) as $path) {
                    @unlink($path);
                }
                $book->disconnectWorksheets();
            }
        }
    }

    public function test_project_chart_translations_preserve_color_keys_and_amounts(): void
    {
        $component = new DashboardProjects;
        $component->total = $component->budgeted = 100;
        $component->executed = 20;
        $component->booked = 40;
        $component->real_value = 30;
        foreach (['en', 'es', 'it'] as $locale) {
            app()->setLocale($locale);
            $chart = $component->columnDataGraphTwo($component->createResumeGraph(), 'Resume');
            $first = $chart->toArray()['data'][0];
            $this->assertSame(__('Budgeted'), $first['title']);
            $this->assertEquals(100, $first['value']);
            $this->assertSame('#5BCA5A', $first['color']);
            $pie = $component->pieDataGraphTwo($component->createResumePieGraph(), 1, 'Account Balance With Booked (Real SAP)');
            $this->assertSame(__('Booked (Real SAP)'), $pie->toArray()['data'][0]['title']);
            $this->assertEquals(30, $pie->toArray()['data'][0]['value']);
        }
    }
}
