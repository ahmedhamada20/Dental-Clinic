<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementJob;
use App\Jobs\SendAppointmentReminderJob;
use App\Jobs\SendBillingDueReminderJob;
use App\Jobs\SendWaitingListNotificationJob;
use App\Models\Patient\Patient;
use App\Models\System\NotificationLog;
use App\Models\System\SystemNotification;
use App\Modules\Notifications\DTOs\AnnouncementNotificationDTO;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ─── Index: history + filter bar ────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters = $request->only(['channel', 'notification_type', 'status', 'date_from', 'date_to', 'patient_id']);

        $logs = NotificationLog::query()
            ->when(! empty($filters['channel']),           fn ($q) => $q->where('channel', $filters['channel']))
            ->when(! empty($filters['notification_type']), fn ($q) => $q->where('notification_type', $filters['notification_type']))
            ->when(! empty($filters['status']),            fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['date_from']),         fn ($q) => $q->whereDate('sent_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),           fn ($q) => $q->whereDate('sent_at', '<=', $filters['date_to']))
            ->when(! empty($filters['patient_id']),        fn ($q) => $q->where('notifiable_type', Patient::class)
                                                                         ->where('notifiable_id', $filters['patient_id']))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $stats = [
            'total'     => NotificationLog::count(),
            'sent'      => NotificationLog::where('status', 'sent')->count(),
            'failed'    => NotificationLog::where('status', 'failed')->count(),
            'today'     => NotificationLog::whereDate('created_at', today())->count(),
        ];

        $channels          = ['database', 'in_app', 'email', 'sms', 'push'];
        $notificationTypes = NotificationType::cases();

        return view('admin.notifications.index', compact(
            'logs', 'stats', 'filters', 'channels', 'notificationTypes'
        ));
    }

    // ─── Create announcement form ────────────────────────────────────────────

    public function create(): View
    {
        $channels          = ['database', 'in_app', 'email', 'sms', 'push'];
        $notificationTypes = NotificationType::cases();
        $patients          = Patient::orderBy('full_name')->get(['id', 'full_name', 'email', 'phone']);

        return view('admin.notifications.create', compact('channels', 'notificationTypes', 'patients'));
    }

    // ─── Store: dispatch announcement / bulk ─────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string', 'max:4000'],
            'channels'     => ['required', 'array', 'min:1'],
            'channels.*'   => ['string', 'in:database,in_app,email,sms,push'],
            'audience'     => ['required', 'string', 'in:all_patients,active_patients,patient_ids'],
            'patient_ids'  => ['nullable', 'array'],
            'patient_ids.*'=> ['integer', 'exists:patients,id'],
        ]);

        $dto         = new AnnouncementNotificationDTO(
            title:    $validated['title'],
            body:     $validated['body'],
            channels: $validated['channels'],
        );
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendAnnouncementJob::dispatch($dto, $triggeredBy);

        return redirect()->route('admin.notifications.index')
            ->with('success', __('admin.messages.notifications.announcement_queued'));
    }

    // ─── Show log detail ─────────────────────────────────────────────────────

    public function show(int $id): View
    {
        $log = NotificationLog::with('triggeredBy')->findOrFail($id);

        return view('admin.notifications.show', compact('log'));
    }

    // ─── Manual trigger: appointment reminders ───────────────────────────────

    public function sendAppointmentReminders(Request $request): RedirectResponse
    {
        $channels    = $request->input('channels', ['database', 'email']);
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendAppointmentReminderJob::dispatch(null, $channels, $triggeredBy);

        return back()->with('success', __('admin.messages.notifications.appointment_reminders_queued'));
    }

    // ─── Manual trigger: billing reminders ───────────────────────────────────

    public function sendBillingReminders(Request $request): RedirectResponse
    {
        $channels    = $request->input('channels', ['database', 'email']);
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendBillingDueReminderJob::dispatch(null, $channels, $triggeredBy);

        return back()->with('success', __('admin.messages.notifications.billing_reminders_queued'));
    }

    // ─── Manual trigger: waiting-list slot notification ──────────────────────

    public function sendWaitingListNotification(Request $request, int $waitingListRequestId): RedirectResponse
    {
        $channels    = $request->input('channels', ['database', 'sms']);
        $triggeredBy = (int) $request->user()?->getAuthIdentifier();

        SendWaitingListNotificationJob::dispatch($waitingListRequestId, $channels, $triggeredBy);

        return back()->with('success', __('admin.messages.notifications.waiting_list_notification_queued'));
    }
}
