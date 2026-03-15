<?php

namespace App\Modules\Notifications\Services;

use App\Enums\NotificationType;
use App\Jobs\SendAnnouncementJob;
use App\Jobs\SendAppointmentReminderJob;
use App\Jobs\SendBillingDueReminderJob;
use App\Jobs\SendWaitingListNotificationJob;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use App\Models\System\DeviceToken;
use App\Models\System\NotificationLog;
use App\Models\System\SystemNotification;
use App\Modules\Notifications\DTOs\AnnouncementNotificationDTO;
use App\Modules\Notifications\DTOs\BulkNotificationDTO;
use App\Modules\Notifications\DTOs\ManualPatientNotificationDTO;
use App\Modules\Notifications\DTOs\NotificationListFilterDTO;
use App\Modules\Notifications\DTOs\RegisterDeviceTokenDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(
        private readonly NotificationBuilderService $builderService,
        private readonly NotificationLogService $logService,
        private readonly FirebasePushService $firebasePushService,
        private readonly NotificationDispatcher $dispatcher,
    ) {
    }

    public function listForPatient(int $patientId, NotificationListFilterDTO $dto): LengthAwarePaginator
    {
        return SystemNotification::query()
            ->where('notifiable_type', Patient::class)
            ->where('notifiable_id', $patientId)
            ->when($dto->unreadOnly === true, fn ($q) => $q->whereNull('read_at'))
            ->latest('id')
            ->paginate($dto->perPage);
    }

    public function listForAdmin(NotificationListFilterDTO $dto, ?int $patientId = null): LengthAwarePaginator
    {
        return SystemNotification::query()
            ->where('notifiable_type', Patient::class)
            ->when($patientId, fn ($q) => $q->where('notifiable_id', $patientId))
            ->when($dto->unreadOnly === true, fn ($q) => $q->whereNull('read_at'))
            ->latest('id')
            ->paginate($dto->perPage);
    }

    public function markAsReadForPatient(Request $request, int $patientId, int $notificationId): SystemNotification
    {
        $notification = SystemNotification::query()
            ->where('id', $notificationId)
            ->where('notifiable_type', Patient::class)
            ->where('notifiable_id', $patientId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now(), 'status' => 'read']);
        }

        $this->logService->log($request, 'notification.mark_read', SystemNotification::class, (int) $notification->id, [
            'read_at' => optional($notification->read_at)?->toIso8601String(),
        ]);

        return $notification->fresh();
    }

    public function markAsReadForAdmin(Request $request, int $notificationId): SystemNotification
    {
        $notification = SystemNotification::query()->findOrFail($notificationId);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now(), 'status' => 'read']);
        }

        $this->logService->log($request, 'notification.mark_read_admin', SystemNotification::class, (int) $notification->id, [
            'read_at' => optional($notification->read_at)?->toIso8601String(),
        ]);

        return $notification->fresh();
    }

    public function markAllAsReadForPatient(Request $request, int $patientId): int
    {
        $updated = SystemNotification::query()
            ->where('notifiable_type', Patient::class)
            ->where('notifiable_id', $patientId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
            ]);

        $this->logService->log($request, 'notification.mark_all_read', Patient::class, $patientId, [
            'count' => $updated,
        ]);

        return $updated;
    }

    public function registerDeviceToken(Request $request, int $patientId, RegisterDeviceTokenDTO $dto): DeviceToken
    {
        $token = DeviceToken::query()->updateOrCreate(
            [
                'patient_id' => $patientId,
                'firebase_token' => $dto->firebaseToken,
            ],
            [
                'device_type' => $dto->deviceType,
                'device_name' => $dto->deviceName,
                'app_version' => $dto->appVersion,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        $this->logService->log($request, 'device_token.register', DeviceToken::class, (int) $token->id, [
            'patient_id' => $patientId,
            'device_type' => $dto->deviceType,
        ]);

        return $token;
    }

    public function removeDeviceToken(Request $request, int $patientId, int $id): void
    {
        $token = DeviceToken::query()
            ->where('id', $id)
            ->where('patient_id', $patientId)
            ->firstOrFail();

        $token->delete();

        $this->logService->log($request, 'device_token.delete', DeviceToken::class, $id, [], [
            'patient_id' => $patientId,
        ]);
    }

    public function notifyPatient(Request $request, int $patientId, ManualPatientNotificationDTO $dto): SystemNotification
    {
        return DB::transaction(function () use ($request, $patientId, $dto) {
            $patient = Patient::query()->findOrFail($patientId);

            $log = $this->dispatcher->dispatch(
                patient: $patient,
                title: $dto->title,
                body: $dto->body,
                channel: $dto->channel,
                type: NotificationType::GENERAL->value,
                data: [],
                triggeredBy: (int) $request->user()?->getAuthIdentifier(),
                triggeredByType: 'manual',
            );

            $notification = $log->systemNotification;

            if (! $notification) {
                $notification = $this->builderService->create(
                    notifiableType: Patient::class,
                    notifiableId: $patientId,
                    title: $dto->title,
                    body: $dto->body,
                    channel: $dto->channel,
                    type: NotificationType::GENERAL->value,
                    data: []
                );
            }

            $this->logService->log($request, 'notification.send_manual', SystemNotification::class, (int) $notification->id, [
                'patient_id' => $patientId,
                'channel' => $dto->channel,
                'notification_log_id' => $log->id,
            ]);

            return $notification;
        });
    }

    // ===================== Workflow Methods =====================

    /**
     * Send a bulk notification to a set of patients (queued).
     */
    public function sendBulk(Request $request, BulkNotificationDTO $dto): void
    {
        $patients = match ($dto->audience) {
            'all_patients'    => Patient::all(),
            'active_patients' => Patient::where('status', 'active')->get(),
            'patient_ids'     => Patient::whereIn('id', $dto->patientIds)->get(),
            default           => collect(),
        };

        foreach ($patients as $patient) {
            foreach ($dto->channels as $channel) {
                $this->dispatcher->dispatch(
                    patient:         $patient,
                    title:           $dto->title,
                    body:            $dto->body,
                    channel:         $channel,
                    type:            $dto->type,
                    data:            [],
                    triggeredBy:     (int) $request->user()?->getAuthIdentifier(),
                    triggeredByType: 'manual',
                );
            }
        }

        $this->logService->log($request, 'notification.send_bulk', Patient::class, 0, [
            'audience'  => $dto->audience,
            'channels'  => $dto->channels,
            'type'      => $dto->type,
            'count'     => $patients->count(),
        ]);
    }

    /**
     * Send a custom announcement to all active patients (queued job).
     */
    public function sendAnnouncement(Request $request, AnnouncementNotificationDTO $dto): void
    {
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendAnnouncementJob::dispatch($dto, $triggeredBy);

        $this->logService->log($request, 'notification.send_announcement', Patient::class, 0, [
            'title'    => $dto->title,
            'channels' => $dto->channels,
        ]);
    }

    /**
     * Queue a billing due reminder for one invoice or all overdue.
     */
    public function sendBillingReminder(Request $request, ?int $invoiceId = null, array $channels = ['database', 'email']): void
    {
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendBillingDueReminderJob::dispatch($invoiceId, $channels, $triggeredBy);

        $this->logService->log($request, 'notification.send_billing_reminder', Invoice::class, $invoiceId ?? 0, [
            'channels' => $channels,
        ]);
    }

    /**
     * Notify a waiting list patient that a slot is available.
     */
    public function sendWaitingListSlot(Request $request, int $waitingListRequestId, array $channels = ['database', 'sms']): void
    {
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendWaitingListNotificationJob::dispatch($waitingListRequestId, $channels, $triggeredBy);

        $this->logService->log($request, 'notification.send_waiting_list_slot', WaitingListRequest::class, $waitingListRequestId, [
            'channels' => $channels,
        ]);
    }

    /**
     * Send appointment reminders (all tomorrow or specific appointment).
     */
    public function sendAppointmentReminder(Request $request, ?int $appointmentId = null, array $channels = ['database', 'email']): void
    {
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendAppointmentReminderJob::dispatch($appointmentId, $channels, $triggeredBy);

        $this->logService->log($request, 'notification.send_appointment_reminder', SystemNotification::class, $appointmentId ?? 0, [
            'channels' => $channels,
        ]);
    }

    // ===================== Notification Log History =====================

    /**
     * Paginated notification dispatch history.
     */
    public function listLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return NotificationLog::query()
            ->when(! empty($filters['channel']),           fn ($q) => $q->where('channel', $filters['channel']))
            ->when(! empty($filters['notification_type']), fn ($q) => $q->where('notification_type', $filters['notification_type']))
            ->when(! empty($filters['status']),            fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['date_from']),         fn ($q) => $q->whereDate('sent_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),           fn ($q) => $q->whereDate('sent_at', '<=', $filters['date_to']))
            ->when(! empty($filters['patient_id']),        fn ($q) => $q->where('notifiable_type', Patient::class)
                                                                         ->where('notifiable_id', $filters['patient_id']))
            ->latest('id')
            ->paginate($perPage);
    }
}
