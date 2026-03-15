<?php

namespace App\Modules\Notifications\DTOs;

final class AnnouncementNotificationDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly string  $body,
        public readonly array   $channels,   // database, email, sms, push
        public readonly ?string $scheduledAt = null,
    ) {}

    public static function fromArray(array $v): self
    {
        return new self(
            title:       $v['title'],
            body:        $v['body'],
            channels:    $v['channels'],
            scheduledAt: $v['scheduled_at'] ?? null,
        );
    }
}

