<?php

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment\Appointment;
use App\Models\Patient\Patient;
use App\Modules\Notifications\Services\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendAppointmentReminderJob
 *
 * Dispatched daily (via console schedule) to send reminders to patients
 * whose appointment is the next day.  Can also be dispatched immediately
 * for a specific appointment.
 */
class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    /**
     * @param  int|null  $appointmentId  If null, processes ALL tomorrow's appointments.
     * @param  string[]  $channels
     */
    public function __construct(
        public readonly ?int   $appointmentId = null,
        public readonly array  $channels      = ['database', 'email'],
        public readonly ?int   $triggeredBy   = null,
    ) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $appointments = $this->appointmentId
            ? Appointment::where('id', $this->appointmentId)->with('patient')->get()
            : Appointment::query()
                ->whereDate('appointment_date', now()->addDay()->toDateString())
                ->whereIn('status', [
                    AppointmentStatus::CONFIRMED->value,
                    AppointmentStatus::PENDING->value,
                ])
                ->with('patient')
                ->get();

        foreach ($appointments as $appt) {
            /** @var Patient|null $patient */
            $patient = $appt->patient;
            if (! $patient) {
                continue;
            }

            $date = optional($appt->appointment_date)->format('D, M j Y');
            $time = $appt->start_time ? " at {$appt->start_time}" : '';

            foreach ($this->channels as $channel) {
                try {
                    $dispatcher->dispatch(
                        patient:         $patient,
                        title:           'Appointment Reminder',
                        body:            "Dear {$patient->full_name}, this is a reminder for your appointment on {$date}{$time}.",
                        channel:         $channel,
                        type:            NotificationType::APPOINTMENT_REMINDER->value,
                        data:            ['appointment_id' => $appt->id, 'appointment_date' => (string) $appt->appointment_date],
                        triggeredBy:     $this->triggeredBy,
                        triggeredByType: $this->triggeredBy ? 'manual' : 'scheduled',
                    );
                } catch (\Throwable $e) {
                    Log::error('SendAppointmentReminderJob error', [
                        'appointment_id' => $appt->id,
                        'channel'        => $channel,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

