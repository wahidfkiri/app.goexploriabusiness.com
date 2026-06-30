<?php

use Illuminate\Support\Facades\Route;
use Vendor\ModeEmploi\Controllers\ModeEmploiController;

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/mode-emploi', [ModeEmploiController::class, 'index'])->name('mode-emploi.index');

    // Modules
    Route::post('/mode-emploi/modules', [ModeEmploiController::class, 'storeModule'])->name('mode-emploi.modules.store');
    Route::put('/mode-emploi/modules/{module}', [ModeEmploiController::class, 'updateModule'])->name('mode-emploi.modules.update');
    Route::delete('/mode-emploi/modules/{module}', [ModeEmploiController::class, 'destroyModule'])->name('mode-emploi.modules.destroy');

    // Lessons
    Route::post('/mode-emploi/lessons', [ModeEmploiController::class, 'storeLesson'])->name('mode-emploi.lessons.store');
    Route::put('/mode-emploi/lessons/{lesson}', [ModeEmploiController::class, 'updateLesson'])->name('mode-emploi.lessons.update');
    Route::delete('/mode-emploi/lessons/{lesson}', [ModeEmploiController::class, 'destroyLesson'])->name('mode-emploi.lessons.destroy');
});
