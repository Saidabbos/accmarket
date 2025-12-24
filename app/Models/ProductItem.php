<?php

namespace App\Models;

use App\Enums\ProductItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'content',
        'status',
        'reserved_at',
        'sold_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', ProductItemStatus::AVAILABLE->value);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', ProductItemStatus::RESERVED->value);
    }

    public function scopeSold($query)
    {
        return $query->where('status', ProductItemStatus::SOLD->value);
    }

    public function reserve(Order $order): void
    {
        $this->update([
            'status' => ProductItemStatus::RESERVED->value,
            'order_id' => $order->id,
            'reserved_at' => now(),
        ]);
    }

    public function markAsSold(): void
    {
        $this->update([
            'status' => ProductItemStatus::SOLD->value,
            'sold_at' => now(),
        ]);
    }

    public function release(): void
    {
        $this->update([
            'status' => ProductItemStatus::AVAILABLE->value,
            'order_id' => null,
            'reserved_at' => null,
        ]);
    }

    public function isAvailable(): bool
    {
        return $this->status === ProductItemStatus::AVAILABLE->value;
    }

    public function isSold(): bool
    {
        return $this->status === ProductItemStatus::SOLD->value;
    }

    public function isReserved(): bool
    {
        return $this->status === ProductItemStatus::RESERVED->value;
    }
}
