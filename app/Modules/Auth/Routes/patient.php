<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes - Patient
|--------------------------------------------------------------------------
|
| Patient authentication routes (login, register, logout, etc.)
|
*/

Route::post('register', [\App\Modules\Auth\Controllers\PatientAuthController::class, 'register']);
Route::post('login', [\App\Modules\Auth\Controllers\PatientAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [\App\Modules\Auth\Controllers\PatientAuthController::class, 'logout']);
    Route::get('me', [\App\Modules\Auth\Controllers\PatientAuthController::class, 'me']);
    Route::post('change-password', [\App\Modules\Auth\Controllers\PatientAuthController::class, 'changePassword']);
});

