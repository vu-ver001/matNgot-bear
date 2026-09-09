<?php

namespace App\Services;

use App\Models\Order;

class VietQrService
{
    protected string $bankCode;
    protected string $bankName;
    protected string $accountNumber;
    protected string $accountName;

    public function __construct(?PaymentSettingService $settingService = null)
    {
        $settingService = $settingService ?? app(PaymentSettingService::class);
        $settings = $settingService->getSettings();

        $this->bankCode = $settings['vietqr_bank_code'];
        $this->bankName = $settings['vietqr_bank_name'];
        $this->accountNumber = $settings['vietqr_account_number'];
        $this->accountName = $settings['vietqr_account_name'];
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
