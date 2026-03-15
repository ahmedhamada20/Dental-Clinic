<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Billing Routes - Patient
|--------------------------------------------------------------------------
|
| Patient access to invoices and payment history
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Invoices
    Route::get('invoices', [\App\Modules\Billing\Controllers\Patient\PatientInvoiceController::class, 'index']);
    Route::get('invoices/{id}', [\App\Modules\Billing\Controllers\Patient\PatientInvoiceController::class, 'show']);
    Route::get('invoices/{id}/pdf', [\App\Modules\Billing\Controllers\Patient\PatientInvoiceController::class, 'download']);

    // Payments
    Route::get('payments', [\App\Modules\Billing\Controllers\Patient\PatientPaymentController::class, 'index']);
    Route::get('payments/{id}', [\App\Modules\Billing\Controllers\Patient\PatientPaymentController::class, 'show']);
});

