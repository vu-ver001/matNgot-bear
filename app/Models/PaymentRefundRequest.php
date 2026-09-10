<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefundRequest extends Model
{
    protected $fillable = [
        'payment_id',
        'order_id',
        'requested_by',
        'amount',
        'reason',
        'proof_image',
        'bank_name',
        'bank_account',
        'account_holder',
        'status',
        'admin_note',
        'approved_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate VietQR Napas247 URL for Admin to scan and transfer refund
     */
    public function getVietQrUrlAttribute(): ?string
    {
        if (empty($this->bank_name) || empty($this->bank_account)) {
            return null;
        }

        $bankCode = trim($this->bank_name);
        $accountNo = trim($this->bank_account);
        $amount = (int) $this->amount;
        $orderCode = $this->order?->order_code ?? 'MNB';
        $content = rawurlencode("Hoan tien don {$orderCode}");
        $accountName = rawurlencode($this->account_holder ?? '');

        // Standard VietQR QuickLink format
        return "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$content}&accountName={$accountName}";
    }
}
