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
        'cancel_request_status',
        'cancel_request_reason',
        'cancel_requested_at',
        'cancel_rejection_reason',
        'refund_bank_name',
        'refund_bank_account',
        'refund_account_holder',
        'refund_note',
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
        'cancel_requested_at' => 'datetime',
    ];

    public function hasPendingCancelRequest(): bool
    {
        return $this->cancel_request_status === 'PENDING';
    }

    public function isCancelApproved(): bool
    {
        return $this->cancel_request_status === 'APPROVED';
    }

    public function isCancelRejected(): bool
    {
        return $this->cancel_request_status === 'REJECTED';
    }

    public function canCancelDirectly(): bool
    {
        return $this->order_status === 'PENDING';
    }

    public function canRequestCancel(): bool
    {
        return $this->order_status === 'CONFIRMED' && ! $this->hasPendingCancelRequest();
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->canCancelDirectly() || $this->canRequestCancel();
    }

    public function needsRefund(): bool
    {
        return $this->order_status === 'CANCELLED' && $this->payment_status === 'PAID';
    }

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
}
