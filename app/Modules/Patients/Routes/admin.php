<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patients Routes - Admin
|--------------------------------------------------------------------------
|
| Admin management of patients
|
*/

Route::apiResource('patients', \App\Modules\Patients\Controllers\PatientController::class);

// Patient medical history and emergency contacts
Route::put('patients/{patient}/medical-history', [\App\Modules\Patients\Controllers\PatientController::class, 'updateMedicalHistory']);
Route::post('patients/{patient}/emergency-contacts', [\App\Modules\Patients\Controllers\PatientController::class, 'addEmergencyContact']);
Route::get('patients/{patient}/emergency-contacts', [\App\Modules\Patients\Controllers\PatientController::class, 'getEmergencyContacts']);

