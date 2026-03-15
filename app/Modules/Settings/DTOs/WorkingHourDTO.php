<?php

namespace App\Modules\Settings\DTOs;

final class WorkingHourDTO
{
    public function __construct(
        public readonly string $day,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly bool $isActive
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            day: $validated['day'],
            startTime: $validated['start_time'],
            endTime: $validated['end_time'],
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }
}
