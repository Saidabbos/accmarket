<?php

namespace App\Enums;

enum ProductItemStatus: string
{
    case AVAILABLE = 'available';
    case SOLD = 'sold';
    case RESERVED = 'reserved';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Available',
            self::SOLD => 'Sold',
            self::RESERVED => 'Reserved',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
