<?php

namespace App\Jobs;

use App\Enums\NotificationType;
use App\Models\Patient\Patient;
use App\Modules\Notifications\DTOs\AnnouncementNotificationDTO;
use App\Modules\Notifications\Services\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendAnnouncementJob
 *
 * Broadcasts a custom announcement to all active patients across the chosen channels.
 */
class SendAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly AnnouncementNotificationDTO $dto,
        public readonly ?int $triggeredBy = null,
    ) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $patients = Patient::query()->where('status', 'active')->get();

        foreach ($patients as $patient) {
            foreach ($this->dto->channels as $channel) {
                try {
                    $dispatcher->dispatch(
                        patient:         $patient,
                        title:           $this->dto->title,
                        body:            $this->dto->body,
                        channel:         $channel,
                        type:            NotificationType::CUSTOM_ANNOUNCEMENT->value,
                        data:            [],
                        triggeredBy:     $this->triggeredBy,
                        triggeredByType: 'manual',
                    );
                } catch (\Throwable $e) {
                    Log::error('SendAnnouncementJob error', [
                        'patient_id' => $patient->id,
                        'channel'    => $channel,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

