<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash   = 'cash';
    case QRIS   = 'qris';
    case Card   = 'card';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::Cash     => 'Tunai',
            self::QRIS     => 'QRIS',
            self::Card     => 'Kartu Debit/Kredit',
            self::Transfer => 'Transfer Bank',
        };
    }
}