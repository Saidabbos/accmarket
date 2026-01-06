<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'guest_email',
        'guest_token',
        'product_id',
        'order_number',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'payment_status',
        'payment_id',
        'payment_method',
        'payment_url',
        'nowpayments_id',
        'paid_at',
        'completed_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productItems(): HasMany
    {
        return $this->hasMany(ProductItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function isGuestOrder(): bool
    {
        return is_null($this->buyer_id) && !is_null($this->guest_email);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->buyer?->email ?? $this->guest_email;
    }

    public function markAsPaid(string $paymentId, string $paymentMethod): void
    {
        $this->update([
            'status' => 'paid',
            'payment_status' => 'completed',
            'payment_id' => $paymentId,
            'payment_method' => $paymentMethod,
            'paid_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
        $this->productItems()->each(fn ($item) => $item->release());
    }
}
