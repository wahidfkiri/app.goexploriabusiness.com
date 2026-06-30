<?php

use Illuminate\Support\Facades\Route;
use Vendor\LocationDataEngine\Controllers\Admin\DashboardController;
use Vendor\LocationDataEngine\Controllers\Admin\LogsController;
use Vendor\LocationDataEngine\Controllers\Admin\ResultsController;

Route::prefix((string) config('location-data-engine.admin.prefix', 'admin/location-data-engine'))
    ->middleware((array) config('location-data-engine.admin.middleware', ['web', 'auth', 'location-data-engine.admin']))
    ->name('location-data-engine.admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/results', [ResultsController::class, 'index'])->name('results.index');
        Route::get('/results/{businessLocation}', [ResultsController::class, 'show'])->name('results.show');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    });
