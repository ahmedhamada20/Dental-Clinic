<?php

use App\Jobs\SendAppointmentReminderJob;
use App\Jobs\SendBillingDueReminderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Notification Automation ──────────────────────────────────────────────────

// Send appointment reminders every day at 9:00 AM for tomorrow's appointments.
Schedule::call(function () {
    SendAppointmentReminderJob::dispatch(null, ['database', 'email'], null);
})->dailyAt('09:00')->name('appointment-reminders')->withoutOverlapping();

// Send billing due reminders every Monday at 8:00 AM for overdue invoices.
Schedule::call(function () {
    SendBillingDueReminderJob::dispatch(null, ['database', 'email'], null);
})->weeklyOn(1, '08:00')->name('billing-due-reminders')->withoutOverlapping();

