<?php

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case DOCTOR = 'doctor';
    case RECEPTIONIST = 'receptionist';
    case ASSISTANT = 'assistant';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::DOCTOR => 'Doctor',
            self::RECEPTIONIST => 'Receptionist',
            self::ASSISTANT => 'Assistant',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::ADMIN]);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::DOCTOR, self::RECEPTIONIST, self::ADMIN, self::ASSISTANT], true);
    }
}

