<?php

use App\Modules\Settings\Controllers\Admin\ClinicSettingsController;
use App\Modules\Settings\Controllers\Admin\HolidayController;
use App\Modules\Settings\Controllers\Admin\WorkingDaysController;
use App\Modules\Settings\Controllers\Admin\WorkingHoursController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::get('clinic', [ClinicSettingsController::class, 'show']);
    Route::put('clinic', [ClinicSettingsController::class, 'update']);

    Route::get('working-days', [WorkingDaysController::class, 'index']);
    Route::put('working-days', [WorkingDaysController::class, 'update']);

    Route::get('working-hours', [WorkingHoursController::class, 'index']);
    Route::post('working-hours', [WorkingHoursController::class, 'store']);
    Route::put('working-hours/{id}', [WorkingHoursController::class, 'update']);
    Route::delete('working-hours/{id}', [WorkingHoursController::class, 'destroy']);
});

Route::get('holidays', [HolidayController::class, 'index']);
Route::post('holidays', [HolidayController::class, 'store']);
Route::put('holidays/{id}', [HolidayController::class, 'update']);
Route::delete('holidays/{id}', [HolidayController::class, 'destroy']);
