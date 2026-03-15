<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes - Admin
|--------------------------------------------------------------------------
|
| Admin authentication routes
|
*/

Route::post('login', [\App\Modules\Auth\Controllers\AdminAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [\App\Modules\Auth\Controllers\AdminAuthController::class, 'logout']);
    Route::get('me', [\App\Modules\Auth\Controllers\AdminAuthController::class, 'me']);
});

