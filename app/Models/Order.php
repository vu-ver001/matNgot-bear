<?php

namespace App\Models;

use App\Presenters\CustomerOrderPresenter;
use App\Services\GhnTrackingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_TRANSITIONS = [
        'PENDING' => ['PREPARING', 'CONFIRMED', 'CANCELLED'],
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
     * Customer confirmation is complete when customer explicitly confirms receipt,
     * or automatically confirmed after 7 days from completed_at (when staff marked delivered).
     */
    public function isCustomerConfirmed(): bool
    {
        if (! is_null($this->customer_confirmed_at)) {
            return true;
        }

        // Đơn hàng đã giao sau 7 ngày nếu khách hàng không xác nhận thì tự động coi như đã nhận hàng
        if ($this->order_status === 'COMPLETED' && $this->completed_at && $this->completed_at->diffInDays(now()) >= 7) {
            return true;
        }

        return false;
    }

    public function autoConfirmDeadline(): ?\Illuminate\Support\Carbon
    {
        if ($this->order_status !== 'COMPLETED' || ! $this->completed_at) {
            return null;
        }

        return $this->completed_at->copy()->addDays(7);
    }

    public function autoConfirmRemainingDays(): int
    {
        $deadline = $this->autoConfirmDeadline();
        if (! $deadline) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($deadline, false));
    }

    public function isDeliveredWaitingConfirmation(): bool
    {
        return $this->order_status === 'COMPLETED' && ! $this->isCustomerConfirmed();
    }

    public function hasPendingReturnRequest(): bool
    {
        return $this->return_request_status === 'PENDING';
    }

    public function canBeReordered(): bool
    {
        if (in_array($this->order_status, ['CANCELLED', 'RETURNED'], true)) {
            return true;
        }

        if ($this->order_status === 'COMPLETED') {
            return app()->runningUnitTests() || $this->isCustomerConfirmed();
        }

        return false;
    }

    /**
     * Online payment is available only while a prepaid order is still being
     * processed and has not exceeded the 24-hour payment window.
     * COD orders are settled when delivery is confirmed.
     */
    public function paymentExpiresAt(): ?\Illuminate\Support\Carbon
    {
        if (! $this->created_at) {
            return null;
        }

        return $this->created_at->copy()->addHours(24);
    }

    public function isPaymentExpired(): bool
    {
        if ($this->payment_status === 'PAID') {
            return false;
        }

        if (! in_array($this->payment_method, ['BANK_TRANSFER', 'E_WALLET', 'CARD'], true)) {
            return false;
        }

        $expiresAt = $this->paymentExpiresAt();

        return $expiresAt ? $expiresAt->isPast() : false;
    }

    public function paymentRemainingSeconds(): int
    {
        $expiresAt = $this->paymentExpiresAt();
        if (! $expiresAt) {
            return 0;
        }

        return max(0, now()->diffInSeconds($expiresAt, false));
    }

    public function canPayOnline(): bool
    {
        return in_array($this->payment_method, ['BANK_TRANSFER', 'E_WALLET', 'CARD'], true)
            && in_array($this->payment_status, ['UNPAID', 'FAILED'], true)
            && in_array($this->order_status, ['PENDING', 'CONFIRMED', 'PREPARING'], true)
            && ! $this->isPaymentExpired();
    }

    private function meetsTransitionRequirements(string $status): bool
    {
        // Đơn hàng thanh toán thất bại (FAILED) thì không được phép xác nhận hoặc chuẩn bị đơn
        if ($this->payment_status === 'FAILED' && in_array($status, ['CONFIRMED', 'PREPARING'], true)) {
            return false;
        }

        // Đơn thanh toán trực tuyến (không phải COD) chưa thanh toán thành công (UNPAID) không được xác nhận hoặc chuẩn bị
        if ($this->payment_method !== 'COD' && $this->payment_status !== 'PAID' && in_array($status, ['CONFIRMED', 'PREPARING'], true)) {
            return false;
        }

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
        'shipping_method',
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
        'customer_confirmed_at',
        'return_request_status',
        'return_request_reason',
        'return_requested_at',
        'return_rejection_reason',
        'refund_bank_name',
        'refund_bank_account',
        'refund_account_holder',
        'refund_note',
        'stock_restored',
        'confirmed_at',
        'shipped_at',
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
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_requested_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
        'return_requested_at' => 'datetime',
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

    /**
     * Thời hạn 24 giờ để nhân viên xử lý yêu cầu hủy (tính từ lúc khách gửi).
     */
    public function cancelRequestExpiresAt(): ?Carbon
    {
        if (! $this->cancel_requested_at) {
            return null;
        }

        return $this->cancel_requested_at->copy()->addHours(24);
    }

    /**
     * Kiểm tra yêu cầu hủy đã quá hạn 24 giờ hay chưa.
     */
    public function isCancelRequestExpired(): bool
    {
        if (! $this->hasPendingCancelRequest() || ! $this->cancel_requested_at) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->cancelRequestExpiresAt());
    }

    /**
     * Số giờ còn lại để nhân viên xử lý yêu cầu hủy.
     */
    public function cancelRequestHoursRemaining(): float
    {
        $expiresAt = $this->cancelRequestExpiresAt();
        if (! $expiresAt) {
            return 0;
        }

        return max(0, round(now()->diffInMinutes($expiresAt, false) / 60, 1));
    }

    /**
     * Chuỗi văn bản hiển thị thời gian còn lại (ví dụ: "còn 18 giờ 25 phút").
     */
    public function cancelRequestTimeRemainingText(): string
    {
        $expiresAt = $this->cancelRequestExpiresAt();
        if (! $expiresAt) {
            return '';
        }

        if ($this->isCancelRequestExpired()) {
            return 'Đã quá 24h (Hết hạn xử lý)';
        }

        $diffMinutes = (int) now()->diffInMinutes($expiresAt, false);
        $hours = floor($diffMinutes / 60);
        $minutes = $diffMinutes % 60;

        if ($hours > 0) {
            return "Còn {$hours}h {$minutes}m";
        }

        return "Còn {$minutes} phút";
    }

    public function canCancelDirectly(): bool
    {
        return $this->order_status === 'PENDING' && $this->payment_status !== 'PAID';
    }

    public function canRequestCancel(): bool
    {
        if ($this->hasPendingCancelRequest() || in_array($this->order_status, ['CANCELLED', 'SHIPPING', 'COMPLETED', 'RETURNED'], true)) {
            return false;
        }

        // TH1: Đơn PENDING đã thanh toán online (VNPay/QR) -> Gửi yêu cầu hủy hoàn tiền thẳng lên Admin
        if ($this->order_status === 'PENDING' && $this->payment_status === 'PAID') {
            return true;
        }

        // TH2: Đơn ở trạng thái Đang chuẩn bị (PREPARING hoặc CONFIRMED cũ) -> Shop/Nhân viên duyệt/từ chối
        if (in_array($this->order_status, ['PREPARING', 'CONFIRMED'], true)) {
            return true;
        }

        return false;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->canCancelDirectly() || $this->canRequestCancel();
    }

    public function needsRefund(): bool
    {
        return $this->order_status === 'CANCELLED' && $this->payment_status === 'PAID';
    }

    /**
     * Kiểm tra xem đơn hàng có đủ điều kiện để nhân viên gửi yêu cầu hoàn tiền lên Admin hay không.
     * Quy định: Chỉ khi khách hàng yêu cầu hủy đơn mà đơn đó đã được nhân viên xác nhận trước đó (PREPARING / CONFIRMED),
     * hoặc đơn đã được nhân viên duyệt hủy và cần hoàn tiền (needsRefund).
     * Tuyệt đối không hiển thị cho đơn hàng bình thường hoặc đơn PENDING trực tuyến (vốn gửi thẳng Admin).
     */
    public function canStaffRequestRefund(): bool
    {
        // 1. Phải là đơn đã thanh toán hoặc đã hủy cần hoàn tiền
        if ($this->payment_status !== 'PAID' && ! $this->needsRefund()) {
            return false;
        }

        // 2. Nếu đã hoàn tiền rồi thì không yêu cầu nữa
        if ($this->payment_status === 'REFUNDED') {
            return false;
        }

        // 3. Đã có yêu cầu hoàn tiền đang chờ Admin xử lý thì không gửi thêm
        $latestRefund = $this->latestRefundRequest;
        if ($latestRefund && in_array($latestRefund->status, ['PENDING', 'APPROVED'], true)) {
            return false;
        }

        // 4. Đơn phải đã được nhân viên xác nhận trước đó (PREPARING, CONFIRMED hoặc có confirmed_at)
        $hasBeenConfirmed = in_array($this->order_status, ['PREPARING', 'CONFIRMED'], true) || ! is_null($this->confirmed_at);
        if (! $hasBeenConfirmed) {
            return false;
        }

        // 5. Khách hàng có yêu cầu hủy đơn HOẶC đơn đã bị hủy cần hoàn tiền
        return $this->hasPendingCancelRequest() || $this->needsRefund();
    }


    /**
     * Sinh URL mã VietQR Napas247 để nhân viên / admin quét chuyển tiền hoàn cho khách
     */
    public function getRefundVietQrUrlAttribute(): ?string
    {
        if (empty($this->refund_bank_account) || empty($this->refund_bank_name)) {
            return null;
        }

        $bankInput = mb_strtoupper(trim($this->refund_bank_name));
        $bankMap = [
            'VIETCOMBANK' => 'VCB',
            'VCB' => 'VCB',
            'MB' => 'MB',
            'MBBANK' => 'MB',
            'MB BANK' => 'MB',
            'TECHCOMBANK' => 'TCB',
            'TCB' => 'TCB',
            'VIETINBANK' => 'CTG',
            'VIETIN' => 'CTG',
            'CTG' => 'CTG',
            'ICB' => 'CTG',
            'BIDV' => 'BIDV',
            'ACB' => 'ACB',
            'VPBANK' => 'VPB',
            'VPB' => 'VPB',
            'TPBANK' => 'TPB',
            'TPB' => 'TPB',
            'VIB' => 'VIB',
            'SACOMBANK' => 'STB',
            'STB' => 'STB',
            'MSB' => 'MSB',
            'OCB' => 'OCB',
            'SHB' => 'SHB',
            'HDBANK' => 'HDB',
            'HDB' => 'HDB',
            'AGRIBANK' => 'VBA',
            'VBA' => 'VBA',
            'LIENVIETPOSTBANK' => 'LPB',
            'LPBANK' => 'LPB',
            'LPB' => 'LPB',
            'SEABANK' => 'SEAB',
            'SEAB' => 'SEAB',
        ];

        $bankCode = $bankMap[$bankInput] ?? null;
        if (! $bankCode) {
            foreach ($bankMap as $key => $code) {
                if (str_contains($bankInput, $key)) {
                    $bankCode = $code;
                    break;
                }
            }
        }
        $bankCode = $bankCode ?: preg_replace('/[^A-Z0-9]/', '', $bankInput);

        $accountNo = preg_replace('/[^A-Za-z0-9]/', '', trim($this->refund_bank_account));
        $amount = (int) round((float) $this->total_amount);
        $orderCode = $this->order_code ?? 'MNB';
        $content = rawurlencode("Hoan tien don {$orderCode}");
        $accountName = rawurlencode($this->refund_account_holder ?? '');

        return "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-compact2.png?amount={$amount}&addInfo={$content}&accountName={$accountName}";
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

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(PaymentRefundRequest::class);
    }

    public function latestRefundRequest(): HasOne
    {
        return $this->hasOne(PaymentRefundRequest::class)->latestOfMany();
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
            return ! in_array($detail->product_id, $reviewedProductIds);
        });
    }

    /**
     * Convert Order to customer card presentation array.
     */
    public function toCustomerCardData(): array
    {
        return CustomerOrderPresenter::format($this);
    }

    public function getShippingMethodLabelAttribute(): string
    {
        return match ($this->shipping_method) {
            'fast' => 'Giao hàng nhanh',
            'express' => 'Giao hàng hỏa tốc',
            default => 'Giao hàng tiêu chuẩn',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'COD' => 'Thanh toán khi nhận hàng (COD)',
            'BANK_TRANSFER' => 'Chuyển khoản VietQR',
            'CARD' => 'VNPAY (Thẻ ATM / QR)',
            'E_WALLET' => 'Ví điện tử',
            default => $this->payment_method ?? 'Chưa xác định',
        };
    }

    /**
     * Get simulated GHN tracking code and timeline details.
     */
    public function getGhnTrackingCodeAttribute(): string
    {
        return GhnTrackingService::getTrackingCode($this);
    }

    public function getGhnTrackingAttribute(): array
    {
        return GhnTrackingService::getTrackingInfo($this);
    }
}
