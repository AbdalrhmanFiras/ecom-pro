<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';//Done
    case PROCESSING = 'processing';//
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


}