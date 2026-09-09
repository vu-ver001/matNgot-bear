<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'status',
        'amount',
        'transaction_ref',
        'gateway_response',
        'proof_image',
        'note',
        'confirmed_by',
        'paid_at',
        'cod_reconciled_at',
        'cod_reconciled_by',
        'cod_settled_at',
        'cod_settled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cod_reconciled_at' => 'datetime',
        'cod_settled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function reconciledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_reconciled_by');
    }

    public function settledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_settled_by');
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(PaymentRefundRequest::class);
    }

    public function latestRefundRequest(): HasOne
    {
        return $this->hasOne(PaymentRefundRequest::class)->latestOfMany();
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image) {
            return null;
        }

        if (str_starts_with($this->proof_image, 'http://') || str_starts_with($this->proof_image, 'https://')) {
            return $this->proof_image;
        }

        return Storage::disk('public')->url($this->proof_image);
    }
}
