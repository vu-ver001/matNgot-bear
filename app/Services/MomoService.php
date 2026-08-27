<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    protected string $partnerCode;
    protected string $accessKey;
    protected string $secretKey;
    protected string $endpoint;
    protected string $phone;
    protected string $accountName;

    public function __construct()
    {
        $this->partnerCode = config('services.momo.partner_code', env('MOMO_PARTNER_CODE', 'MOMOBK01'));
        $this->accessKey = config('services.momo.access_key', env('MOMO_ACCESS_KEY', ''));
        $this->secretKey = config('services.momo.secret_key', env('MOMO_SECRET_KEY', ''));
        $this->endpoint = config('services.momo.endpoint', env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'));
        $this->phone = config('services.momo.phone', env('MOMO_PHONE', '0377466205'));
        $this->accountName = config('services.momo.name', env('MOMO_NAME', 'NGUYỄN NGỌC ANH'));
    }

    /**
     * Get merchant / receiver configurations
     */
    public function getConfig(): array
    {
        return [
            'momo_phone' => $this->phone,
            'momo_name' => $this->accountName,
            'partner_code' => $this->partnerCode,
        ];
    }

    /**
     * Generate standard MoMo QR Code URL.
     * Uses MoMo official universal link format (https://nhantien.momo.vn/{phone})
     * to avoid the deprecated P2P string error ("Ví chưa xác thực").
     */
    public function generateQrUrl(Order $order): string
    {
        $momoLink = "https://nhantien.momo.vn/{$this->phone}";
        return "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($momoLink);
    }

    /**
     * Get direct MoMo app deep link
     */
    public function getMomoDeepLink(Order $order): string
    {
        return "https://nhantien.momo.vn/{$this->phone}";
    }

    /**
     * Create MoMo Gateway Online Payment Request (AIO / ATM / QR).
     */
    public function createGatewayPayment(Order $order, string $returnUrl, string $notifyUrl): array
    {
        if (empty($this->accessKey) || empty($this->secretKey)) {
            return [
                'success' => false,
                'message' => 'MoMo Gateway credentials are not fully configured. Using QR direct payment mode.',
                'payUrl' => null,
                'qrUrl' => $this->generateQrUrl($order)
            ];
        }

        $requestId = $this->partnerCode . '_' . time() . '_' . uniqid();
        $orderId = $order->order_code . '_' . time();
        $orderInfo = "Thanh toán đơn hàng {$order->order_code} tại Mật Ngọt Bear";
        $amount = (string) (int) $order->total_amount;
        $extraData = base64_encode(json_encode(['order_id' => $order->id]));
        $requestType = "captureWallet";

        // Generate HMAC SHA256 Signature according to MoMo API specs
        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $notifyUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $this->partnerCode .
            "&redirectUrl=" . $returnUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "Mật Ngọt Bear",
            'storeId' => "MatNgotBearStore",
            'requestId' => $requestId,
            'amount' => (int) $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $notifyUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        try {
            $response = Http::post($this->endpoint, $data);
            $json = $response->json();

            if (isset($json['resultCode']) && $json['resultCode'] == 0) {
                return [
                    'success' => true,
                    'payUrl' => $json['payUrl'] ?? $json['shortLink'] ?? null,
                    'qrCodeUrl' => $json['qrCodeUrl'] ?? null,
                    'deeplink' => $json['deeplink'] ?? null,
                ];
            }

            Log::warning('MoMo Gateway Error:', $json ?? []);
            return [
                'success' => false,
                'message' => $json['message'] ?? 'Lỗi khởi tạo giao dịch MoMo',
                'qrUrl' => $this->generateQrUrl($order)
            ];
        } catch (\Throwable $e) {
            Log::error('MoMo Gateway Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'qrUrl' => $this->generateQrUrl($order)
            ];
        }
    }

    /**
     * Verify MoMo IPN signature
     */
    public function verifyIpnSignature(array $data): bool
    {
        if (empty($data['signature']) || empty($this->secretKey) || empty($this->accessKey)) {
            return false;
        }

        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . ($data['amount'] ?? '') .
            "&extraData=" . ($data['extraData'] ?? '') .
            "&message=" . ($data['message'] ?? '') .
            "&orderId=" . ($data['orderId'] ?? '') .
            "&orderInfo=" . ($data['orderInfo'] ?? '') .
            "&orderType=" . ($data['orderType'] ?? '') .
            "&partnerCode=" . ($data['partnerCode'] ?? '') .
            "&payType=" . ($data['payType'] ?? '') .
            "&requestId=" . ($data['requestId'] ?? '') .
            "&responseTime=" . ($data['responseTime'] ?? '') .
            "&resultCode=" . ($data['resultCode'] ?? '') .
            "&transId=" . ($data['transId'] ?? '');

        $computedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return hash_equals($computedSignature, $data['signature']);
    }
}
