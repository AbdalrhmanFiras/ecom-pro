<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';//Done
    case PROCESSING = 'processing';//Done
    case SHIPPED = 'shipped';//Done
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';//Done
    case REFUNDED = 'refunded';//Done
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


}