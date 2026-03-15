<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Billing Routes - Admin
|--------------------------------------------------------------------------
|
| Admin management of invoices, payments, and promotions
|
*/

// Promotions management
Route::apiResource('promotions', \App\Modules\Billing\Controllers\Admin\PromotionAdminController::class);
Route::post('promotions/{id}/toggle-status', [\App\Modules\Billing\Controllers\Admin\PromotionAdminController::class, 'toggleStatus']);

// Invoices management
Route::apiResource('invoices', \App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, ['only' => ['index', 'store', 'show', 'update']]);
Route::post('invoices/{id}/items', [\App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, 'addItem']);
Route::delete('invoice-items/{id}', [\App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, 'deleteItem']);
Route::post('invoices/{id}/finalize', [\App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, 'finalize']);
Route::post('invoices/{id}/cancel', [\App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, 'cancel']);
Route::get('invoices/{id}/pdf', [\App\Modules\Billing\Controllers\Admin\InvoiceAdminController::class, 'pdf']);

// Payments management
Route::apiResource('payments', \App\Modules\Billing\Controllers\Admin\PaymentAdminController::class, ['only' => ['index', 'show', 'destroy']]);
Route::post('invoices/{id}/payments', [\App\Modules\Billing\Controllers\Admin\PaymentAdminController::class, 'store']);

