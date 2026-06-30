<?php

use Illuminate\Support\Facades\Route;
use Vendor\MapsDataEngine\Controllers\Api\ListingsController;
use Vendor\MapsDataEngine\Controllers\Api\ReferenceController;
use Vendor\MapsDataEngine\Controllers\Api\ScanController;

Route::prefix((string) config('maps-data-engine.admin.prefix', 'admin/maps-data-engine') . '/api')
    ->middleware((array) config('maps-data-engine.admin.middleware', ['web', 'auth']))
    ->name('maps-data-engine.api.')
    ->group(function (): void {
        Route::post('/scan-sessions', [ScanController::class, 'store'])->name('scan-sessions.store');
        Route::get('/scan-sessions/{mapScanSession}', [ScanController::class, 'status'])->name('scan-sessions.status');
        Route::get('/scan-sessions/{mapScanSession}/logs', [ScanController::class, 'logs'])->name('scan-sessions.logs');
        Route::get('/infrastructure', [ScanController::class, 'infrastructure'])->name('infrastructure');

        Route::get('/listings', [ListingsController::class, 'index'])->name('listings.index');
        Route::get('/listings/export/csv', [ListingsController::class, 'exportCsv'])->name('listings.export.csv');
        Route::get('/listings/export/excel', [ListingsController::class, 'exportExcel'])->name('listings.export.excel');
        Route::get('/listings/{mapBusinessListing}', [ListingsController::class, 'show'])->name('listings.show');

        Route::get('/reference/{level}', [ReferenceController::class, 'locations'])->name('reference.locations');
    });
