<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::PAID;
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
