<?php

namespace App\Modules\Notifications\DTOs;

final class ManualPatientNotificationDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $channel
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            title: $validated['title'],
            body: $validated['body'],
            channel: $validated['channel'],
        );
    }
}
