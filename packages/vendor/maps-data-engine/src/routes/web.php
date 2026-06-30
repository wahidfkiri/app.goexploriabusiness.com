<?php

use Illuminate\Support\Facades\Route;
use Vendor\MapsDataEngine\Controllers\Admin\DashboardController;
use Vendor\MapsDataEngine\Controllers\Admin\LogsController;
use Vendor\MapsDataEngine\Controllers\Admin\ResultsController;

Route::prefix((string) config('maps-data-engine.admin.prefix', 'admin/maps-data-engine'))
    ->middleware((array) config('maps-data-engine.admin.middleware', ['web', 'auth']))
    ->name('maps-data-engine.admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/results', [ResultsController::class, 'index'])->name('results.index');
        Route::get('/results/{mapBusinessListing}', [ResultsController::class, 'show'])->name('results.show');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    });
