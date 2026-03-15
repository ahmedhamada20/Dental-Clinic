<?php

use App\Modules\Notifications\Controllers\AdminNotificationController;
use Illuminate\Support\Facades\Route;

// ── Existing notification routes ─────────────────────────────────────────────
Route::get('notifications', [AdminNotificationController::class, 'index']);
Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead']);
Route::post('patients/{id}/notify', [AdminNotificationController::class, 'notifyPatient']);

// ── Workflow / automation actions ─────────────────────────────────────────────
Route::post('notifications/send-bulk', [AdminNotificationController::class, 'sendBulk']);
Route::post('notifications/send-announcement', [AdminNotificationController::class, 'sendAnnouncement']);
Route::post('notifications/send-appointment-reminders', [AdminNotificationController::class, 'sendAppointmentReminders']);
Route::post('notifications/send-billing-reminders', [AdminNotificationController::class, 'sendBillingReminders']);
Route::post('notifications/waiting-list/{waitingListRequestId}/notify', [AdminNotificationController::class, 'sendWaitingListSlot']);

// ── Notification logs ─────────────────────────────────────────────────────────
Route::get('notification-logs', [AdminNotificationController::class, 'logs']);

