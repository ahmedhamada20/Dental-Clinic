<?php

use App\Modules\Notifications\Controllers\PatientNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [PatientNotificationController::class, 'index']);

});
