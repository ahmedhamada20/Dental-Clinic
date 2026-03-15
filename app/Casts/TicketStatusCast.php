<?php

namespace App\Casts;

use App\Enums\TicketStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TicketStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?TicketStatus
    {
        return TicketStatus::fromStoredValue($value !== null ? (string) $value : null);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof TicketStatus) {
            return $value->toDatabaseValue();
        }

        return TicketStatus::fromStoredValue((string) $value)?->toDatabaseValue() ?? (string) $value;
    }
}

