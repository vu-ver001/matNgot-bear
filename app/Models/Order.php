<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'customer_id',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'note',
        'voucher_id',
        'shipping_voucher_id',
        'subtotal',
        'discount_amount',
        'shipping_discount_amount',
        'shipping_fee',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'cancel_reason',
        'cancelled_by',
        'stock_restored',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'stock_restored' => 'boolean',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id')->withTrashed();
    }

    public function shippingVoucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'shipping_voucher_id')->withTrashed();
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if order has any completed product that has not yet been reviewed.
     */
    public function hasUnreviewedProducts(): bool
    {
        if ($this->order_status !== 'COMPLETED') {
            return false;
        }

        $reviewedProductIds = $this->reviews->pluck('product_id')->all();
        return $this->details->contains(function ($detail) use ($reviewedProductIds) {
            return !in_array($detail->product_id, $reviewedProductIds);
        });
    }

    /**
     * Convert Order to customer card presentation array.
     */
    public function toCustomerCardData(): array
    {
        return \App\Presenters\CustomerOrderPresenter::format($this);
    }
}
