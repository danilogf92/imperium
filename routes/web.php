<?php

use App\Http\Controllers\Data\RedirectToProjectDataController;
use App\Http\Controllers\ExcelTemplateDownloadController;
use App\Livewire\Data\IndexData;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Orders\Ordenes;
use App\Livewire\Planification\Planification;
use App\Livewire\Project\DashboardProjects;
use App\Livewire\Project\IndexProject;
use App\Livewire\Resume\Resume;
use App\Livewire\Task\TaskTable;
use App\Livewire\Templates\TemplateLibrary;
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

    Route::get('/task', TaskTable::class)
        ->name('task');

    Route::get('/orders', Ordenes::class)
        ->name('orders');

    Route::get('/planification', Planification::class)
        ->name('planification');

    Route::get('/resume', Resume::class)
        ->name('resume');

    Route::get('/templates', TemplateLibrary::class)
        ->name('templates');

    Route::get('/templates/{excelTemplate}/download', ExcelTemplateDownloadController::class)
        ->name('templates.download');

    Route::prefix('projects')->group(function (): void {
        Route::get('/', IndexProject::class)
            ->name('projects');

        Route::prefix('{project}')->name('projects.')->group(function (): void {
            Route::get('/dashboard', DashboardProjects::class)
                ->name('dashboard');

            Route::get('/data', IndexData::class)
                ->name('data');

            Route::get('/orders', Ordenes::class)
                ->name('orders');
        });
    });
});

require __DIR__ . '/auth.php';
