<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\DTOs\AnnouncementNotificationDTO;
use App\Modules\Notifications\DTOs\BulkNotificationDTO;
use App\Modules\Notifications\DTOs\ManualPatientNotificationDTO;
use App\Modules\Notifications\DTOs\NotificationListFilterDTO;
use App\Modules\Notifications\Requests\AdminNotificationListRequest;
use App\Modules\Notifications\Requests\SendAnnouncementRequest;
use App\Modules\Notifications\Requests\SendBulkNotificationRequest;
use App\Modules\Notifications\Requests\SendManualPatientNotificationRequest;
use App\Modules\Notifications\Resources\NotificationLogResource;
use App\Modules\Notifications\Resources\NotificationResource;
use App\Modules\Notifications\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    // ─── System notifications list ───────────────────────────────────────────

    public function index(AdminNotificationListRequest $request): JsonResponse
    {
        $dto = NotificationListFilterDTO::fromArray($request->validated());
        $patientId = $request->validated()['patient_id'] ?? null;

        $paginator = $this->notificationService->listForAdmin($dto, $patientId);

        return ApiResponse::success([
            'items' => NotificationResource::collection(collect($paginator->items())),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], 'Notifications retrieved successfully.');
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = $this->notificationService->markAsReadForAdmin($request, $id);

        return ApiResponse::success(
            new NotificationResource($notification),
            'Notification marked as read.'
        );
    }

    public function notifyPatient(SendManualPatientNotificationRequest $request, int $id): JsonResponse
    {
        $dto          = ManualPatientNotificationDTO::fromArray($request->validated());
        $notification = $this->notificationService->notifyPatient($request, $id, $dto);

        return ApiResponse::success(
            new NotificationResource($notification),
            'Patient notified successfully.',
            201
        );
    }

    // ─── Workflow Actions ────────────────────────────────────────────────────

    /**
     * Send a bulk notification to a configured audience.
     */
    public function sendBulk(SendBulkNotificationRequest $request): JsonResponse
    {
        $dto = BulkNotificationDTO::fromArray($request->validated());
        $this->notificationService->sendBulk($request, $dto);

        return ApiResponse::success(null, 'Bulk notification dispatched.');
    }

    /**
     * Broadcast a custom announcement to all active patients.
     */
    public function sendAnnouncement(SendAnnouncementRequest $request): JsonResponse
    {
        $dto = AnnouncementNotificationDTO::fromArray($request->validated());
        $this->notificationService->sendAnnouncement($request, $dto);

        return ApiResponse::success(null, 'Announcement queued for dispatch.', 202);
    }

    /**
     * Trigger appointment reminders manually.
     * ?appointment_id=X  — target a single appointment, omit for all tomorrow.
     */
    public function sendAppointmentReminders(Request $request): JsonResponse
    {
        $appointmentId = $request->query('appointment_id') ? (int) $request->query('appointment_id') : null;
        $channels      = $request->input('channels', ['database', 'email']);

        $this->notificationService->sendAppointmentReminder($request, $appointmentId, $channels);

        return ApiResponse::success(null, 'Appointment reminder(s) queued.', 202);
    }

    /**
     * Trigger billing due reminders manually.
     * ?invoice_id=X  — target a single invoice, omit for all overdue.
     */
    public function sendBillingReminders(Request $request): JsonResponse
    {
        $invoiceId = $request->query('invoice_id') ? (int) $request->query('invoice_id') : null;
        $channels  = $request->input('channels', ['database', 'email']);

        $this->notificationService->sendBillingReminder($request, $invoiceId, $channels);

        return ApiResponse::success(null, 'Billing reminder(s) queued.', 202);
    }

    /**
     * Notify a waiting-list patient that a slot is available.
     */
    public function sendWaitingListSlot(Request $request, int $waitingListRequestId): JsonResponse
    {
        $channels = $request->input('channels', ['database', 'sms']);

        $this->notificationService->sendWaitingListSlot($request, $waitingListRequestId, $channels);

        return ApiResponse::success(null, 'Waiting-list slot notification queued.', 202);
    }

    // ─── Notification Logs ───────────────────────────────────────────────────

    /**
     * Paginated notification dispatch history.
     */
    public function logs(Request $request): JsonResponse
    {
        $filters = $request->only(['channel', 'notification_type', 'status', 'date_from', 'date_to', 'patient_id']);
        $perPage = (int) ($request->query('per_page', 20));

        $paginator = $this->notificationService->listLogs($filters, $perPage);

        return ApiResponse::success([
            'items' => NotificationLogResource::collection(collect($paginator->items())),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ], 'Notification logs retrieved.');
    }
}
