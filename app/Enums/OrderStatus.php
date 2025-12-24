<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function canBeReviewed(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
