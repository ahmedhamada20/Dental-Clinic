<?php

use App\Modules\Visits\Controllers\AdminVisitController;
use App\Modules\Visits\Controllers\AdminVisitNoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('visits')->group(function () {
    Route::get('/', [AdminVisitController::class, 'index']);
    Route::get('{id}', [AdminVisitController::class, 'show'])->whereNumber('id');
    Route::post('{id}/start', [AdminVisitController::class, 'start'])->whereNumber('id');
    Route::post('{id}/complete', [AdminVisitController::class, 'complete'])->whereNumber('id');
    Route::post('{id}/notes', [AdminVisitNoteController::class, 'store'])->whereNumber('id');
});

Route::prefix('visit-notes')->group(function () {
    Route::put('{id}', [AdminVisitNoteController::class, 'update'])->whereNumber('id');
    Route::delete('{id}', [AdminVisitNoteController::class, 'destroy'])->whereNumber('id');
});
