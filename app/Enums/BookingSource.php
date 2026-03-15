<?php

namespace App\Enums;

enum BookingSource: string
{
    case PHONE = 'phone';
    case MOBILE_APP = 'mobile_app';
    case WEB_APP = 'dashboard';
    case WALK_IN = 'walk_in';
    case EMAIL = 'email';
    case REFERRAL = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Phone',
            self::MOBILE_APP => 'Mobile App',
            self::WEB_APP => 'dashboard',
            self::WALK_IN => 'Walk-in',
            self::EMAIL => 'Email',
            self::REFERRAL => 'Referral',
        };
    }
}

