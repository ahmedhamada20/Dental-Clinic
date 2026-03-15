<?php

use App\Http\Controllers\Admin\OdontogramController;
use App\Http\Controllers\Admin\OdontogramHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('/visits/{visit}/odontogram', [OdontogramController::class, 'store'])
        ->name('admin.visits.odontogram.store');

    Route::get('/visits/{visit}/odontogram/history', [OdontogramHistoryController::class, 'index'])
        ->name('admin.visits.odontogram-history.index');
});

