<?php

namespace App\Services;

use App\Models\Order;

class VnpayService
{
    protected string $tmnCode;
    protected string $hashSecret;
    protected string $vnpUrl;
    protected string $merchantName;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code', env('VNPAY_TMN_CODE', 'MNBEAR01'));
        $this->hashSecret = config('services.vnpay.hash_secret', env('VNPAY_HASH_SECRET', ''));
        $this->vnpUrl = config('services.vnpay.url', env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'));
        $this->merchantName = config('services.vnpay.merchant_name', env('VNPAY_MERCHANT_NAME', 'MẬT NGỌT BEAR'));
    }

    /**
     * Get merchant configurations
     */
    public function getConfig(): array
    {
        return [
            'vnpay_merchant' => $this->merchantName,
            'vnpay_tmn_code' => $this->tmnCode,
        ];
    }

    /**
     * Generate standard VNPAY QR Code image URL.
     */
    public function generateQrUrl(Order $order): string
    {
        $amount = (int) $order->total_amount;
        $transferContent = $order->order_code;

        // Standard EMVCo / VietQR payload formatted for VNPay & Mobile Banking
        $payload = "00020101021238540010A00000072701240006970422011003774662050208QRIBFTTA5303704540" .
            strlen((string)$amount) . $amount .
            "5802VN5914NGUYEN NGOC ANH6006HA NOI62" .
            (strlen($transferContent) + 4) . "08" . sprintf('%02d', strlen($transferContent)) . $transferContent . "6304";

        return "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($payload);
    }

    /**
     * Build VNPAY Gateway URL if merchant secret is configured.
     */
    public function createPaymentUrl(Order $order, string $returnUrl, string $ipAddress = '127.0.0.1'): string
    {
        $vnp_Params = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->tmnCode,
            "vnp_Amount" => (int) $order->total_amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $ipAddress,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan don hang {$order->order_code} tai Mat Ngot Bear",
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $returnUrl,
            "vnp_TxnRef" => $order->order_code,
        ];

        ksort($vnp_Params);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($vnp_Params as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnpUrl . "?" . $query;
        if (!empty($this->hashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->hashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }
}
