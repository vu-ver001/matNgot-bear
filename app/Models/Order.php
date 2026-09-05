<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_TRANSITIONS = [
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['PREPARING', 'CANCELLED'],
        'PREPARING' => ['SHIPPING', 'CANCELLED'],
        'SHIPPING' => ['COMPLETED', 'CANCELLED'],
        'COMPLETED' => ['RETURNED'],
        'RETURNED' => [],
        'CANCELLED' => [],
    ];

    public function allowedNextStatuses(): array
    {
        return array_values(array_filter(
            self::STATUS_TRANSITIONS[$this->order_status] ?? [],
            fn (string $status) => $this->meetsTransitionRequirements($status)
        ));
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedNextStatuses(), true);
    }

    /**
     * A reorder is meaningful only after the original order has reached a
     * terminal state. This prevents a customer from duplicating an order that
     * is still being processed or shipped.
     */
    public function canBeReordered(): bool
    {
        return in_array($this->order_status, ['COMPLETED', 'CANCELLED', 'RETURNED'], true);
    }

    /**
     * Online payment is available only while a prepaid order is still being
     * processed. COD orders are settled when delivery is confirmed.
     */
    public function canPayOnline(): bool
    {
        return in_array($this->payment_method, ['BANK_TRANSFER', 'E_WALLET', 'CARD'], true)
            && in_array($this->payment_status, ['UNPAID', 'FAILED'], true)
            && in_array($this->order_status, ['PENDING', 'CONFIRMED', 'PREPARING'], true);
    }

    private function meetsTransitionRequirements(string $status): bool
    {
        return $status !== 'SHIPPING'
            || $this->payment_method === 'COD'
            || $this->payment_status === 'PAID';
    }

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
