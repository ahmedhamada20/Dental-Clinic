<?php

namespace App\Modules\Notifications\DTOs;

final class NotificationListFilterDTO
{
    public function __construct(
        public readonly ?bool $unreadOnly,
        public readonly int $perPage
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            unreadOnly: isset($validated['unread_only']) ? (bool) $validated['unread_only'] : null,
            perPage: (int) ($validated['per_page'] ?? 15),
        );
    }
}
