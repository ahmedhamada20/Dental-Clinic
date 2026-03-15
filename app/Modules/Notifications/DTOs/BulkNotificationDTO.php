<?php

namespace App\Modules\Notifications\DTOs;

final class BulkNotificationDTO
{
    /**
     * @param  string   $title
     * @param  string   $body
     * @param  string   $type      NotificationType value
     * @param  string[] $channels  e.g. ['email','push']
     * @param  string   $audience  all_patients|active_patients|patient_ids|custom
     * @param  int[]    $patientIds  used when audience = 'patient_ids'
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $type,
        public readonly array  $channels,
        public readonly string $audience,
        public readonly array  $patientIds = [],
    ) {}

    public static function fromArray(array $v): self
    {
        return new self(
            title:      $v['title'],
            body:       $v['body'],
            type:       $v['type'],
            channels:   $v['channels'],
            audience:   $v['audience'],
            patientIds: $v['patient_ids'] ?? [],
        );
    }
}

