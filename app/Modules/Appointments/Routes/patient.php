<?php

use App\Modules\Appointments\Controllers\Patient\AppointmentController;
use App\Modules\Appointments\Controllers\Patient\WaitingListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Appointments Routes - Patient
|--------------------------------------------------------------------------
|
| Patient booking and management of appointments
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Appointments endpoints
    Route::prefix('appointments')->group(function () {
        Route::get('available-slots', [AppointmentController::class, 'availableSlots']);
        Route::get('', [AppointmentController::class, 'index']);
        Route::post('', [AppointmentController::class, 'store']);
        Route::get('{id}', [AppointmentController::class, 'show']);
        Route::post('{id}/cancel', [AppointmentController::class, 'cancel']);
    });

    // Waiting list endpoints
    Route::prefix('waiting-list')->group(function () {
        Route::post('', [WaitingListController::class, 'store']);
        Route::get('', [WaitingListController::class, 'index']);
        Route::delete('{id}', [WaitingListController::class, 'destroy']);
        Route::post('{id}/claim-slot', [WaitingListController::class, 'claimSlot']);
    });
});
