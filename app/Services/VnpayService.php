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
        // Standard VNPAY Sandbox test credentials for development/testing
        $this->tmnCode = config('services.vnpay.tmn_code', env('VNPAY_TMN_CODE', 'DKEKANL1'));
        $this->hashSecret = config('services.vnpay.hash_secret', env('VNPAY_HASH_SECRET', 'A36K9YROGJFCDMLD8MKRXUE2Q4AKM6TV'));
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
            'vnpay_url' => $this->vnpUrl,
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
     * Build VNPAY Gateway URL for Card / ATM / Visa / QR redirection.
     */
    public function createPaymentUrl(Order $order, ?string $returnUrl = null, string $ipAddress = '127.0.0.1', ?string $customTxnRef = null): string
    {
        $returnUrl = $returnUrl ?? config('services.vnpay.return_url', route('payment.vnpay.return'));
        $createDate = date('YmdHis');
        $expireDate = date('YmdHis', strtotime('+15 minutes', strtotime($createDate)));
        $cleanOrderInfo = "Thanh toan don hang " . preg_replace('/[^A-Za-z0-9]/', '', $order->order_code);

        // VNPay requires valid IPv4. Localhost IPv6 '::1' causes gateway issues.
        $cleanIp = filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ipAddress : '127.0.0.1';

        // Use unique suffix so multiple payment attempts for the same order don't collide with VNPay's 'TxnRef already exists' constraint
        $txnRef = $customTxnRef ?? ($order->order_code . '_' . date('His'));

        $vnp_Params = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->tmnCode,
            "vnp_Amount" => (int) $order->total_amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $createDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $cleanIp,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $cleanOrderInfo,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $returnUrl,
            "vnp_TxnRef" => $txnRef,
            "vnp_ExpireDate" => $expireDate,
        ];

        ksort($vnp_Params);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($vnp_Params as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string) $key) . "=" . urlencode((string) $value);
            } else {
                $hashdata .= urlencode((string) $key) . "=" . urlencode((string) $value);
                $i = 1;
            }
            $query .= urlencode((string) $key) . "=" . urlencode((string) $value) . '&';
        }

        $vnp_Url = $this->vnpUrl . "?" . $query;
        if (!empty($this->hashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->hashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Alias for createPaymentUrl
     */
    public function createPayment(Order $order, ?string $returnUrl = null, string $ipAddress = '127.0.0.1'): string
    {
        return $this->createPaymentUrl($order, $returnUrl, $ipAddress);
    }

    /**
     * Verify VNPAY return response signature.
     * Strictly filters for parameters prefixed with 'vnp_' to prevent extra query or route parameters from corrupting the hash.
     */
    public function verifyResponse(array $inputData): bool
    {
        $vnp_SecureHash = (string) ($inputData['vnp_SecureHash'] ?? '');
        if (empty($vnp_SecureHash) || empty($this->hashSecret)) {
            return false;
        }

        // Only include keys starting with 'vnp_' as defined by VNPay specification
        $vnpData = [];
        foreach ($inputData as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')) {
                $vnpData[$key] = $value;
            }
        }

        unset($vnpData['vnp_SecureHash'], $vnpData['vnp_SecureHashType']);

        ksort($vnpData);
        $i = 0;
        $hashData = "";
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string) $key) . "=" . urlencode((string) $value);
            } else {
                $hashData .= urlencode((string) $key) . "=" . urlencode((string) $value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return hash_equals(strtolower($secureHash), strtolower($vnp_SecureHash));
    }

    /**
     * Generate mock signed response params for testing / sandbox demonstration.
     */
    public function generateMockResponse(Order $order, string $responseCode = '00', ?string $txnRef = null): array
    {
        $params = [
            'vnp_Amount' => ((int) $order->total_amount) * 100,
            'vnp_BankCode' => 'NCB',
            'vnp_BankTranNo' => 'VNP' . date('YmdHis'),
            'vnp_CardType' => 'ATM',
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $order->order_code,
            'vnp_PayDate' => date('YmdHis'),
            'vnp_ResponseCode' => $responseCode,
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_TransactionNo' => (string) rand(10000000, 99999999),
            'vnp_TransactionStatus' => $responseCode,
            'vnp_TxnRef' => $txnRef ?? ($order->order_code . '_' . date('His')),
        ];

        ksort($params);
        $hashData = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string) $k) . "=" . urlencode((string) $v);
            } else {
                $hashData .= urlencode((string) $k) . "=" . urlencode((string) $v);
                $i = 1;
            }
        }

        $params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $this->hashSecret);

        return $params;
    }

    /**
     * Alias for verifyResponse on Return
     */
    public function verifyReturn(array $inputData): bool
    {
        return $this->verifyResponse($inputData);
    }

    /**
     * Alias for verifyResponse on IPN
     */
    public function verifyIpn(array $inputData): bool
    {
        return $this->verifyResponse($inputData);
    }

    /**
     * Get descriptive response message from VNPAY response code.
     */
    public function getResponseMessage(string $code): string
    {
        return match ($code) {
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
            default => 'Giao dịch thất bại hoặc có lỗi không xác định từ cổng VNPAY.',
        };
    }
}
