<?php

use Illuminate\Support\Facades\Route;
use Vendor\GeoMap\Controllers\PlaceController;
use Vendor\GeoMap\Controllers\GeoMapController;
use Vendor\GeoMap\Controllers\GeoDataController;
use Vendor\GeoMap\Controllers\CategoryController;
use Vendor\GeoMap\Controllers\MapCategoryController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/map/categories', [CategoryController::class, 'index'])->name('geomap.categories');
    Route::post('/map/categories/{id}/icon', [CategoryController::class, 'updateIcon'])->name('geomap.categories.icon');
    Route::delete('/map/categories/{id}/icon', [CategoryController::class, 'removeIcon'])->name('geomap.categories.icon.remove');

    Route::get('/map-categories', [MapCategoryController::class, 'index'])->name('geomap.map-categories.index');
    Route::post('/map-categories', [MapCategoryController::class, 'store'])->name('geomap.map-categories.store');
    Route::match(['put', 'post'], '/map-categories/{id}', [MapCategoryController::class, 'update'])->name('geomap.map-categories.update');
    Route::delete('/map-categories/{id}', [MapCategoryController::class, 'destroy'])->name('geomap.map-categories.destroy');
    Route::post('/map-categories/{id}/toggle-status', [MapCategoryController::class, 'toggleStatus'])->name('geomap.map-categories.toggle');
});

// Page principale
Route::get('/map', function () {
    return view('geo-map::map');
});

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/places', [PlaceController::class, 'index']);
    Route::get('/categories', [PlaceController::class, 'categories']);
    Route::get('/header-data/{countryCode}', [GeoDataController::class, 'getHeaderData']);
    Route::get('/provinces/{countryCode}', [GeoDataController::class, 'getProvinces']);
    Route::get('/regions/{provinceId}', [GeoDataController::class, 'getRegions']);
});

Route::get('continent/page/{continentId}', function ($continentId) {
    return view('geo-map::continents.page', compact('continentId'));
});
Route::get('/countrie/{countrieCode}', [GeoMapController::class, 'getCountrie'])
    ->name('geomap.countrie');
Route::get('/countrie/{countrieCode}/{provinceCode}', [GeoMapController::class, 'getProvince'])
    ->name('geomap.province');
Route::get('/country/{countrieCode}', [GeoMapController::class, 'getCountries'])
    ->name('geomap.countries');
