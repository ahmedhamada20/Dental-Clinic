<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patients Routes - Patient
|--------------------------------------------------------------------------
|
| Patient-specific routes for profile and services access
|
*/

// Patient profile endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [\App\Modules\Auth\Controllers\PatientProfileController::class, 'show']);
    Route::put('profile', [\App\Modules\Auth\Controllers\PatientProfileController::class, 'update']);
    Route::get('profile/medical-summary', [\App\Modules\Auth\Controllers\PatientProfileController::class, 'medicalSummary']);
});

// Patient services (public)
Route::get('services', [\App\Modules\Patients\Controllers\PatientServiceController::class, 'index']);
Route::get('services/{service}', [\App\Modules\Patients\Controllers\PatientServiceController::class, 'show']);

