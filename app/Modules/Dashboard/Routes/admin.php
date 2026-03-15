<?php

use App\Modules\Dashboard\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::get('summary', [DashboardController::class, 'summary']);
    Route::get('today-queue', [DashboardController::class, 'todayQueue']);
});
