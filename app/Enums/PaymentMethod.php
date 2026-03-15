<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case INSTAPAY = 'instapay';
    case VODAFONE_CASH = 'vodafone_cash';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::INSTAPAY => 'Instapay',
            self::VODAFONE_CASH => 'Vodafone Cash',
        };
    }
}

