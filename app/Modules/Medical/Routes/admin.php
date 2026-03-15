<?php

use App\Modules\Medical\Controllers\AdminMedicalFileController;
use App\Modules\Medical\Controllers\AdminOdontogramController;
use App\Modules\Medical\Controllers\AdminPrescriptionController;
use App\Modules\Medical\Controllers\AdminTreatmentPlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('patients/{id}')->whereNumber('id')->group(function () {
    Route::get('odontogram', [AdminOdontogramController::class, 'index']);
    Route::post('odontogram/teeth', [AdminOdontogramController::class, 'updateTooth']);
    Route::get('odontogram/history', [AdminOdontogramController::class, 'history']);

    Route::get('treatment-plans', [AdminTreatmentPlanController::class, 'indexByPatient']);
    Route::post('treatment-plans', [AdminTreatmentPlanController::class, 'store']);

    Route::get('medical-files', [AdminMedicalFileController::class, 'indexByPatient']);
    Route::post('medical-files', [AdminMedicalFileController::class, 'store']);
});

Route::prefix('treatment-plans')->group(function () {
    Route::get('{id}', [AdminTreatmentPlanController::class, 'show'])->whereNumber('id');
    Route::put('{id}', [AdminTreatmentPlanController::class, 'update'])->whereNumber('id');
    Route::post('{id}/change-status', [AdminTreatmentPlanController::class, 'changeStatus'])->whereNumber('id');
    Route::post('{id}/items', [AdminTreatmentPlanController::class, 'addItem'])->whereNumber('id');
});

Route::prefix('treatment-plan-items')->group(function () {
    Route::put('{id}', [AdminTreatmentPlanController::class, 'updateItem'])->whereNumber('id');
    Route::post('{id}/complete', [AdminTreatmentPlanController::class, 'completeItem'])->whereNumber('id');
});

Route::prefix('visits/{id}')->whereNumber('id')->group(function () {
    Route::post('prescriptions', [AdminPrescriptionController::class, 'store']);
});

Route::prefix('prescriptions')->group(function () {
    Route::get('{id}', [AdminPrescriptionController::class, 'show'])->whereNumber('id');
    Route::put('{id}', [AdminPrescriptionController::class, 'update'])->whereNumber('id');
    Route::post('{id}/items', [AdminPrescriptionController::class, 'addItem'])->whereNumber('id');
});

Route::prefix('prescription-items')->group(function () {
    Route::put('{id}', [AdminPrescriptionController::class, 'updateItem'])->whereNumber('id');
});

Route::prefix('medical-files')->group(function () {
    Route::get('{id}', [AdminMedicalFileController::class, 'show'])->whereNumber('id');
    Route::put('{id}', [AdminMedicalFileController::class, 'update'])->whereNumber('id');
    Route::delete('{id}', [AdminMedicalFileController::class, 'destroy'])->whereNumber('id');
});
