<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Processing = 'processing';
    case Ready     = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Held      = 'held';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Menunggu',
            self::Processing => 'Diproses',
            self::Ready      => 'Siap',
            self::Completed  => 'Selesai',
            self::Cancelled  => 'Dibatalkan',
            self::Held       => 'Ditahan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending    => 'warning',
            self::Processing => 'info',
            self::Ready      => 'success',
            self::Completed  => 'gray',
            self::Cancelled  => 'danger',
            self::Held       => 'secondary',
        };
    }
}