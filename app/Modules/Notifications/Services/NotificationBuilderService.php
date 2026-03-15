<?php

namespace App\Modules\Notifications\Services;

use App\Models\System\SystemNotification;

class NotificationBuilderService
{
    public function create(
        string $notifiableType,
        int $notifiableId,
        string $title,
        string $body,
        string $channel = 'in_app',
        string $type = 'system',
        array $data = []
    ): SystemNotification {
        return SystemNotification::query()->create([
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'channel' => $channel,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}
