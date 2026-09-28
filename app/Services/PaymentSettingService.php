<?php

namespace App\Services;

use App\Models\PaymentSetting;

class PaymentSettingService
{
    /**
     * Get all payment and gateway settings with fallbacks
     */
    public function getSettings(): array
    {
        return [
            'vietqr_bank_code' => PaymentSetting::get('vietqr_bank_code', config('services.vietqr.bank_code', env('VIETQR_BANK_CODE', 'MB'))),
            'vietqr_bank_name' => PaymentSetting::get('vietqr_bank_name', config('services.vietqr.bank_name', env('VIETQR_BANK_NAME', 'MB Bank (Ngân hàng Quân Đội)'))),
            'vietqr_account_number' => PaymentSetting::get('vietqr_account_number', config('services.vietqr.account_number', env('VIETQR_ACCOUNT_NUMBER', ''))) ?? '',
            'vietqr_account_name' => PaymentSetting::get('vietqr_account_name', config('services.vietqr.account_name', env('VIETQR_ACCOUNT_NAME', ''))) ?? '',
            'sepay_api_key' => PaymentSetting::get('sepay_api_key', config('services.sepay.api_key', env('SEPAY_API_KEY', ''))),
            'sepay_webhook_token' => PaymentSetting::get('sepay_webhook_token', config('services.sepay.webhook_token', env('SEPAY_WEBHOOK_TOKEN', ''))),
            'sepay_active' => (bool) PaymentSetting::get('sepay_active', true),
            'webhook_url' => url('/api/payment/webhook'),
        ];
    }

    /**
     * Get a single setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update settings
     */
    public function saveSettings(array $data): void
    {
        foreach ($data as $key => $val) {
            PaymentSetting::set($key, $val, 'payment', 'Cấu hình cổng thanh toán');
        }
    }
}
