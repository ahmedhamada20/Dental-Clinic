<?php

namespace App\Modules\Settings\DTOs;

final class HolidayDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $date,
        public readonly ?string $description
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            name: $validated['name'],
            date: $validated['date'],
            description: $validated['description'] ?? null,
        );
    }
}
