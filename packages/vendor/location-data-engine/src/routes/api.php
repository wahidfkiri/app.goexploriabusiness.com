<?php

use Illuminate\Support\Facades\Route;
use Vendor\LocationDataEngine\Controllers\Api\BusinessLocationController;
use Vendor\LocationDataEngine\Controllers\Api\ReferenceDataController;
use Vendor\LocationDataEngine\Controllers\Api\ScanController;

Route::prefix((string) config('location-data-engine.admin.prefix', 'admin/location-data-engine') . '/api')
    ->middleware((array) config('location-data-engine.admin.middleware', ['web', 'auth', 'location-data-engine.admin']))
    ->name('location-data-engine.api.')
    ->group(function (): void {
        Route::post('/scan-sessions', [ScanController::class, 'store'])->name('scan-sessions.store');
        Route::get('/scan-sessions/{scanSession}', [ScanController::class, 'status'])->name('scan-sessions.status');
        Route::get('/scan-sessions/{scanSession}/logs', [ScanController::class, 'logs'])->name('scan-sessions.logs');

        Route::get('/business-locations', [BusinessLocationController::class, 'index'])->name('business-locations.index');
        Route::get('/business-locations/export/csv', [BusinessLocationController::class, 'exportCsv'])->name('business-locations.export.csv');
        Route::get('/business-locations/export/excel', [BusinessLocationController::class, 'exportExcel'])->name('business-locations.export.excel');
        Route::get('/business-locations/{businessLocation}', [BusinessLocationController::class, 'show'])->name('business-locations.show');

        Route::get('/reference/{level}', [ReferenceDataController::class, 'locations'])->name('reference.locations');
    });
