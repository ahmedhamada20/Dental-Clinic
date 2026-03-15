<?php

namespace App\Jobs;

use App\Enums\NotificationType;
use App\Models\Appointment\WaitingListRequest;
use App\Modules\Notifications\Services\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendWaitingListNotificationJob
 *
 * Notifies a patient on the waiting list that a slot has become available.
 */
class SendWaitingListNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    /**
     * @param  int       $waitingListRequestId
     * @param  string[]  $channels
     * @param  int|null  $triggeredBy
     */
    public function __construct(
        public readonly int   $waitingListRequestId,
        public readonly array $channels    = ['database', 'sms'],
        public readonly ?int  $triggeredBy = null,
    ) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        /** @var WaitingListRequest $request */
        $request = WaitingListRequest::with('patient', 'service')->findOrFail($this->waitingListRequestId);
        $patient = $request->patient;

        if (! $patient) {
            Log::warning('SendWaitingListNotificationJob: no patient found', [
                'waiting_list_request_id' => $this->waitingListRequestId,
            ]);
            return;
        }

        $serviceName = optional($request->service)->name ?? 'your requested service';
        $date        = optional($request->preferred_date)?->format('D, M j Y') ?? 'soon';

        foreach ($this->channels as $channel) {
            try {
                $dispatcher->dispatch(
                    patient:         $patient,
                    title:           'Slot Available — Waiting List',
                    body:            "Good news, {$patient->full_name}! A slot for {$serviceName} is now available on {$date}. Please call us or book online to confirm.",
                    channel:         $channel,
                    type:            NotificationType::WAITING_LIST_SLOT->value,
                    data:            ['waiting_list_request_id' => $request->id],
                    triggeredBy:     $this->triggeredBy,
                    triggeredByType: $this->triggeredBy ? 'manual' : 'auto',
                );
            } catch (\Throwable $e) {
                Log::error('SendWaitingListNotificationJob error', [
                    'waiting_list_request_id' => $this->waitingListRequestId,
                    'channel'                 => $channel,
                    'error'                   => $e->getMessage(),
                ]);
            }
        }

        // Mark notified_at so we don't re-notify
        $request->update(['notified_at' => now()]);
    }
}

