<?php

use App\Modules\Appointments\Controllers\Admin\AppointmentController;
use App\Modules\Appointments\Controllers\Admin\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Appointments Routes - Admin
|--------------------------------------------------------------------------
|
| Admin management of appointments and tickets
|
*/

// Appointments endpoints
Route::prefix('appointments')->group(function () {
    Route::get('', [AppointmentController::class, 'index']);
    Route::post('', [AppointmentController::class, 'store']);
    Route::get('{id}', [AppointmentController::class, 'show']);
    Route::put('{id}', [AppointmentController::class, 'update']);
    Route::post('{id}/confirm', [AppointmentController::class, 'confirm']);
    Route::post('{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('{id}/check-in', [AppointmentController::class, 'checkIn']);
    Route::post('{id}/mark-no-show', [AppointmentController::class, 'markNoShow']);
    Route::get('{id}/status-logs', [AppointmentController::class, 'statusLogs']);
});

// Ticket/Queue endpoints
Route::prefix('tickets')->group(function () {
    Route::get('today', [TicketController::class, 'today']);
    Route::post('{id}/call', [TicketController::class, 'call']);
    Route::post('{id}/start', [TicketController::class, 'start']);
    Route::post('{id}/finish', [TicketController::class, 'finish']);
});
