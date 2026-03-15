<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('patient')->group(function () {
        require base_path('app/Modules/Auth/Routes/patient.php');
        require base_path('app/Modules/Patients/Routes/patient.php');
        require base_path('app/Modules/Appointments/Routes/patient.php');
        require base_path('app/Modules/Visits/Routes/patient.php');
        require base_path('app/Modules/Medical/Routes/patient.php');
        require base_path('app/Modules/Billing/Routes/patient.php');
        require base_path('app/Modules/Notifications/Routes/patient.php');
    });

    Route::prefix('admin')->middleware(['auth:sanctum', 'verified.admin'])->group(function () {
        require base_path('app/Modules/Auth/Routes/admin.php');
        require base_path('app/Modules/Patients/Routes/admin.php');
        require base_path('app/Modules/Appointments/Routes/admin.php');
        require base_path('app/Modules/Visits/Routes/admin.php');
        require base_path('app/Modules/Medical/Routes/admin.php');
        require base_path('app/Modules/Billing/Routes/admin.php');
        require base_path('app/Modules/Notifications/Routes/admin.php');
        require base_path('app/Modules/Reports/Routes/admin.php');
        require base_path('app/Modules/Settings/Routes/admin.php');
        require base_path('app/Modules/Dashboard/Routes/admin.php');
        require base_path('app/Modules/Audit/Routes/admin.php');
    });
});
