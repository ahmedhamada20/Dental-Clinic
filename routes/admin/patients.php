<?php

use App\Http\Controllers\Admin\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:manage_patients'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index'])->name('admin.patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('admin.patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('admin.patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('admin.patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('admin.patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('admin.patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('admin.patients.destroy');
});

