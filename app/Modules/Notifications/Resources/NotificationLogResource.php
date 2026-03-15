<?php

namespace App\Modules\Notifications\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'system_notification_id'  => $this->system_notification_id,
            'notifiable_type'         => $this->notifiable_type,
            'notifiable_id'           => $this->notifiable_id,
            'channel'                 => $this->channel,
            'notification_type'       => $this->notification_type,
            'title'                   => $this->title,
            'body'                    => $this->body,
            'status'                  => $this->status,
            'error_message'           => $this->error_message,
            'meta'                    => $this->meta,
            'sent_at'                 => optional($this->sent_at)?->toIso8601String(),
            'triggered_by'            => $this->triggered_by,
            'triggered_by_type'       => $this->triggered_by_type,
            'created_at'              => optional($this->created_at)?->toIso8601String(),
        ];
    }
}

