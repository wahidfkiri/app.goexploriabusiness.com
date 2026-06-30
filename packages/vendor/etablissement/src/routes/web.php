<?php

use Illuminate\Support\Facades\Route;
use Vendor\Etablissement\Controllers\EtablissementController;
use Vendor\Etablissement\Controllers\RendezVousController;

Auth::routes();

Route::middleware(['auth','web'])->group(function () {
    // Mono-établissement : page d'accueil de mon établissement
    Route::get('etablissements', [EtablissementController::class, 'index'])
        ->name('etablissements.index');
    Route::get('etablissements/show', [EtablissementController::class, 'show'])
        ->name('etablissements.show');
    Route::get('etablissements/edit', [EtablissementController::class, 'edit'])
        ->name('etablissements.edit');
    Route::put('etablissements', [EtablissementController::class, 'update'])
        ->name('etablissements.update');
    Route::get('etablissements/statistics/data', [EtablissementController::class, 'statistics'])
        ->name('etablissements.statistics');

    // Rendez-vous liés à l'établissement connecté
    Route::get('etablissements/rendez-vous', [RendezVousController::class, 'index'])
        ->name('etablissements.rendezvous.index');
    Route::get('etablissements/rendez-vous/statistics', [RendezVousController::class, 'statistics'])
        ->name('etablissements.rendezvous.statistics');

    Route::prefix('api')->group(function () {
        Route::post('/villes/search', [EtablissementController::class, 'search'])->name('api.villes.search');
        Route::get('/activities', [EtablissementController::class, 'getActivities'])->name('api.activities.index');
        Route::get('/etablissements/rendez-vous/events', [RendezVousController::class, 'events'])->name('api.etablissements.rendezvous.events');
        Route::get('/etablissements/rendez-vous/{id}', [RendezVousController::class, 'show'])->name('api.etablissements.rendezvous.show');
        Route::post('/etablissements/rendez-vous', [RendezVousController::class, 'store'])->name('api.etablissements.rendezvous.store');
        Route::put('/etablissements/rendez-vous/{id}', [RendezVousController::class, 'update'])->name('api.etablissements.rendezvous.update');
        Route::delete('/etablissements/rendez-vous/{id}', [RendezVousController::class, 'destroy'])->name('api.etablissements.rendezvous.destroy');
        Route::patch('/etablissements/rendez-vous/{id}/move', [RendezVousController::class, 'move'])->name('api.etablissements.rendezvous.move');
    });
});
