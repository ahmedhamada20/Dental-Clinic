<?php

use App\Modules\Medical\Controllers\PatientMedicalFileController;
use App\Modules\Medical\Controllers\PatientPrescriptionController;
use App\Modules\Medical\Controllers\PatientTreatmentPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('treatment-plans')->group(function () {
        Route::get('/', [PatientTreatmentPlanController::class, 'index']);
        Route::get('{id}', [PatientTreatmentPlanController::class, 'show'])->whereNumber('id');
    });

    Route::prefix('prescriptions')->group(function () {
        Route::get('/', [PatientPrescriptionController::class, 'index']);
        Route::get('{id}', [PatientPrescriptionController::class, 'show'])->whereNumber('id');
    });

    Route::prefix('medical-files')->group(function () {
        Route::get('/', [PatientMedicalFileController::class, 'index']);
        Route::get('{id}', [PatientMedicalFileController::class, 'show'])->whereNumber('id');
    });
});
