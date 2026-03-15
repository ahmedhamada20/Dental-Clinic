<?php

namespace App\Enums;

enum TicketStatus: string
{
    case ISSUED = 'issued';
    case CALLED = 'called';
    case IN_SERVICE = 'in_service';
    case COMPLETED = 'completed';
    case MISSED = 'missed';
    case CANCELLED = 'cancelled';

    public static function fromStoredValue(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($value) {
            'waiting', self::ISSUED->value => self::ISSUED,
            'called', self::CALLED->value => self::CALLED,
            'with_doctor', self::IN_SERVICE->value => self::IN_SERVICE,
            'done', self::COMPLETED->value => self::COMPLETED,
            'missed', self::MISSED->value => self::MISSED,
            'cancelled', self::CANCELLED->value => self::CANCELLED,
            default => self::from($value),
        };
    }

    public function toDatabaseValue(): string
    {
        return match ($this) {
            self::ISSUED => 'waiting',
            self::CALLED => 'called',
            self::IN_SERVICE => 'with_doctor',
            self::COMPLETED => 'done',
            self::MISSED => 'missed',
            self::CANCELLED => 'cancelled',
        };
    }

    public function databaseValues(): array
    {
        return array_values(array_unique([
            $this->value,
            $this->toDatabaseValue(),
        ]));
    }

    /**
     * @param  array<int, self>  $statuses
     * @return array<int, string>
     */
    public static function databaseValuesFor(array $statuses): array
    {
        return array_values(array_unique(array_merge(
            ...array_map(static fn (self $status) => $status->databaseValues(), $statuses)
        )));
    }

    public function label(): string
    {
        return match ($this) {
            self::ISSUED => 'Issued',
            self::CALLED => 'Called',
            self::IN_SERVICE => 'In Service',
            self::COMPLETED => 'Completed',
            self::MISSED => 'Missed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::ISSUED, self::CALLED, self::IN_SERVICE]);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::COMPLETED, self::MISSED, self::CANCELLED]);
    }
}

