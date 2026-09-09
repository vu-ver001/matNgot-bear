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
            'vietqr_account_number' => PaymentSetting::get('vietqr_account_number', config('services.vietqr.account_number', env('VIETQR_ACCOUNT_NUMBER', '0377466205'))),
            'vietqr_account_name' => PaymentSetting::get('vietqr_account_name', config('services.vietqr.account_name', env('VIETQR_ACCOUNT_NAME', 'NGUYỄN NGỌC ANH'))),
            'sepay_api_key' => PaymentSetting::get('sepay_api_key', config('services.sepay.api_key', env('SEPAY_API_KEY', ''))),
            'sepay_webhook_token' => PaymentSetting::get('sepay_webhook_token', config('services.sepay.webhook_token', env('SEPAY_WEBHOOK_TOKEN', ''))),
            'sepay_active' => (bool) PaymentSetting::get('sepay_active', true),
            'webhook_url' => url('/api/payment/webhook'),

            // MoMo Gateway & Wallet
            'momo_partner_code' => PaymentSetting::get('momo_partner_code', config('services.momo.partner_code', env('MOMO_PARTNER_CODE', 'MOMO'))),
            'momo_access_key' => PaymentSetting::get('momo_access_key', config('services.momo.access_key', env('MOMO_ACCESS_KEY', 'F8BBA842ECF85'))),
            'momo_secret_key' => PaymentSetting::get('momo_secret_key', config('services.momo.secret_key', env('MOMO_SECRET_KEY', 'K951B6PE1waDMi640xX08PD3vg6EkVlz'))),
            'momo_endpoint' => PaymentSetting::get('momo_endpoint', config('services.momo.endpoint', env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'))),
            'momo_phone' => PaymentSetting::get('momo_phone', config('services.momo.phone', env('MOMO_PHONE', '0377466205'))),
            'momo_name' => PaymentSetting::get('momo_name', config('services.momo.name', env('MOMO_NAME', 'NGUYỄN NGỌC ANH'))),
            'momo_active' => (bool) PaymentSetting::get('momo_active', true),
            'momo_return_url' => route('payment.momo.return'),
            'momo_ipn_url' => route('payment.momo.ipn'),
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
