<?php

namespace App\Modules\Settings\DTOs;

final class WorkingDaysDTO
{
    public function __construct(public readonly array $days)
    {
    }

    public static function fromArray(array $validated): self
    {
        return new self(days: $validated['days']);
    }
}
