<?php

namespace App\Modules\Notifications\Services;

use App\Models\Patient\Patient;
use App\Models\System\NotificationLog;
use App\Models\System\SystemNotification;
use Illuminate\Support\Facades\Log;

/**
 * NotificationDispatcher
 *
 * Central dispatcher that:
 *  1. Creates a SystemNotification record
 *  2. Routes to the correct channel service (database, email, sms, push)
 *  3. Writes a NotificationLog entry with success / failure
 */
class NotificationDispatcher
{
    public function __construct(
        private readonly NotificationBuilderService $builder,
        private readonly FirebasePushService        $pushService,
        private readonly EmailNotificationService   $emailService,
        private readonly SmsNotificationService     $smsService,
    ) {}

    /**
     * Dispatch a notification to a single patient via one channel.
     *
     * @param  Patient  $patient
     * @param  string   $title
     * @param  string   $body
     * @param  string   $channel   database|email|sms|push
     * @param  string   $type      NotificationType value
     * @param  array    $data      Extra payload stored on SystemNotification
     * @param  int|null $triggeredBy  User ID that initiated the dispatch
     * @param  string   $triggeredByType  manual|scheduled|auto
     * @return NotificationLog
     */
    public function dispatch(
        Patient $patient,
        string  $title,
        string  $body,
        string  $channel,
        string  $type,
        array   $data            = [],
        ?int    $triggeredBy     = null,
        string  $triggeredByType = 'auto'
    ): NotificationLog {
        // 1. Persist SystemNotification record (for in-app / database channel)
        $sysNotif = null;
        if (in_array($channel, ['database', 'in_app'])) {
            $sysNotif = $this->builder->create(
                notifiableType: Patient::class,
                notifiableId: $patient->id,
                title: $title,
                body: $body,
                channel: $channel,
                type: $type,
                data: $data
            );
        }

        // 2. Route to channel service
        [$status, $error, $meta] = match ($channel) {
            'push'           => $this->dispatchPush($patient, $title, $body, $data),
            'email'          => $this->dispatchEmail($patient, $title, $body),
            'sms'            => $this->dispatchSms($patient, $body),
            'database',
            'in_app'         => ['sent', null, []],
            default          => ['failed', "Unknown channel: {$channel}", []],
        };

        // 3. Write dispatch log
        return NotificationLog::create([
            'system_notification_id' => $sysNotif?->id,
            'notifiable_type'        => Patient::class,
            'notifiable_id'          => $patient->id,
            'channel'                => $channel,
            'notification_type'      => $type,
            'title'                  => $title,
            'body'                   => $body,
            'status'                 => $status,
            'error_message'          => $error,
            'meta'                   => $meta,
            'sent_at'                => $status === 'sent' ? now() : null,
            'triggered_by'           => $triggeredBy,
            'triggered_by_type'      => $triggeredByType,
        ]);
    }

    /**
     * Dispatch to multiple patients across one or more channels.
     *
     * @param  iterable  $patients
     * @param  string    $title
     * @param  string    $body
     * @param  string[]  $channels
     * @param  string    $type
     * @param  array     $data
     * @param  int|null  $triggeredBy
     * @param  string    $triggeredByType
     * @return NotificationLog[]
     */
    public function dispatchBulk(
        iterable $patients,
        string   $title,
        string   $body,
        array    $channels,
        string   $type,
        array    $data            = [],
        ?int     $triggeredBy     = null,
        string   $triggeredByType = 'auto'
    ): array {
        $logs = [];
        foreach ($patients as $patient) {
            foreach ($channels as $channel) {
                $logs[] = $this->dispatch(
                    $patient, $title, $body, $channel, $type,
                    $data, $triggeredBy, $triggeredByType
                );
            }
        }
        return $logs;
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function dispatchPush(Patient $patient, string $title, string $body, array $data): array
    {
        try {
            $this->pushService->sendToPatient($patient->id, $title, $body, $data);
            return ['sent', null, ['patient_id' => $patient->id]];
        } catch (\Throwable $e) {
            Log::error('NotificationDispatcher push failed', ['error' => $e->getMessage()]);
            return ['failed', $e->getMessage(), []];
        }
    }

    private function dispatchEmail(Patient $patient, string $title, string $body): array
    {
        $email = $patient->email ?? null;
        if (! $email) {
            return ['failed', 'Patient has no email address.', []];
        }
        $name = $patient->full_name ?? "{$patient->first_name} {$patient->last_name}";
        $ok   = $this->emailService->send($email, $name, $title, $body);
        return $ok
            ? ['sent', null, ['email' => $email]]
            : ['failed', 'Mail driver returned error — check logs.', ['email' => $email]];
    }

    private function dispatchSms(Patient $patient, string $body): array
    {
        $phone = $patient->phone ?? null;
        if (! $phone) {
            return ['failed', 'Patient has no phone number.', []];
        }
        $ok = $this->smsService->send($phone, $body);
        return $ok
            ? ['sent', null, ['phone' => $phone]]
            : ['failed', 'SMS provider returned error — check logs.', ['phone' => $phone]];
    }
}

