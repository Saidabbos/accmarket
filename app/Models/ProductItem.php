<?php

namespace App\Models;

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
        return $query->where('status', 'available');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function reserve(Order $order): void
    {
        $this->update([
            'status' => 'reserved',
            'order_id' => $order->id,
            'reserved_at' => now(),
        ]);
    }

    public function markAsSold(): void
    {
        $this->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }

    public function release(): void
    {
        $this->update([
            'status' => 'available',
            'order_id' => null,
            'reserved_at' => null,
        ]);
    }
}
