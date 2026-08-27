<?php

namespace App\Services;

use App\Models\Order;

class VietQrService
{
    protected string $bankCode;
    protected string $bankName;
    protected string $accountNumber;
    protected string $accountName;

    public function __construct()
    {
        $this->bankCode = config('services.vietqr.bank_code', env('VIETQR_BANK_CODE', 'MB'));
        $this->bankName = config('services.vietqr.bank_name', env('VIETQR_BANK_NAME', 'MB Bank (Ngân hàng Quân Đội)'));
        $this->accountNumber = config('services.vietqr.account_number', env('VIETQR_ACCOUNT_NUMBER', '0377466205'));
        $this->accountName = config('services.vietqr.account_name', env('VIETQR_ACCOUNT_NAME', 'NGUYỄN NGỌC ANH'));
    }

    /**
     * Get VietQR / MB Bank configurations
     */
    public function getConfig(): array
    {
        return [
            'bank_name' => $this->bankName,
            'bank_code' => $this->bankCode,
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
        ];
    }

    /**
     * Generate Quick VietQR Napas 24/7 image URL with auto-filled amount and note.
     */
    public function generateQrUrl(Order $order): string
    {
        $amount = (int) $order->total_amount;
        $transferContent = $order->order_code;

        return "https://img.vietqr.io/image/{$this->bankCode}-{$this->accountNumber}-compact2.png?amount={$amount}&addInfo=" .
            urlencode($transferContent) . "&accountName=" . urlencode($this->accountName);
    }
}
