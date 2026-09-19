<?php

use App\Http\Controllers\Data\RedirectToProjectDataController;
use App\Http\Controllers\ExcelTemplateDownloadController;
use App\Http\Controllers\ProjectChartExcelExportController;
use App\Livewire\Data\IndexData;
use App\Livewire\Activities\ActivitiesDashboard;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Review;
use App\Livewire\Orders\Ordenes;
use App\Livewire\Planification\Planification;
use App\Livewire\Project\DashboardProjects;
use App\Livewire\Project\IndexProject;
use App\Livewire\Resume\Resume;
use App\Livewire\Task\TaskTable;
use App\Livewire\Templates\TemplateLibrary;
use App\Livewire\Tools\ExcelFilter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome');

/*
|--------------------------------------------------------------------------
| Verified routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/review', Review::class)
    ->middleware(['auth', 'verified'])
    ->name('review');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::view('/profile', 'profile')
        ->name('profile');

    // Backwards-compatible entry point that resolves the latest project.
    Route::get('/data', RedirectToProjectDataController::class)
        ->name('data');

    // Route::get('/task', TaskTable::class)
    //     ->name('task');

    Route::get('/orders', Ordenes::class)
        ->name('orders');

    Route::post('/charts/export-excel', ProjectChartExcelExportController::class)
        ->name('charts.export-excel');

    Route::redirect('/planification', '/planning', 301);
    Route::get('/planning', Planification::class)
        ->name('planification');

    Route::get('/activities', ActivitiesDashboard::class)
        ->name('activities');

    Route::redirect('/resume', '/summary', 301);
    Route::get('/summary', Resume::class)
        ->name('resume');

    Route::get('/templates', TemplateLibrary::class)
        ->name('templates');

    Route::get('/tools', ExcelFilter::class)
        ->name('tools');

    Route::get('/templates/{excelTemplate}/download', ExcelTemplateDownloadController::class)
        ->name('templates.download');

    Route::prefix('projects')->group(function (): void {
        Route::get('/', IndexProject::class)
            ->name('projects');

        Route::get('/{project:slug}/dashboard', DashboardProjects::class)
            ->name('projects.dashboard');

        Route::get('/{project:slug}/data', IndexData::class)
            ->name('projects.data');

        Route::get('/{project:slug}/orders', Ordenes::class)
            ->name('projects.orders');
    });
});

require __DIR__ . '/auth.php';
