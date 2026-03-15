<?php

use App\Modules\Reports\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('appointments', [ReportController::class, 'appointments']);
    Route::get('revenue', [ReportController::class, 'revenue']);
    Route::get('invoices', [ReportController::class, 'invoices']);
    Route::get('patients', [ReportController::class, 'patients']);
    Route::get('services', [ReportController::class, 'services']);
    Route::get('promotions', [ReportController::class, 'promotions']);
    Route::get('doctors', [ReportController::class, 'doctors']);
    Route::get('audit-logs', [ReportController::class, 'auditLogs']);
    Route::get('{reportType}/export/pdf', [ReportController::class, 'exportPdf']);
    Route::get('{reportType}/export/excel', [ReportController::class, 'exportExcel']);
});
