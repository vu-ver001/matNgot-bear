<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SepayService
{
    protected string $apiKey;
    protected string $accountNumber;
    protected string $apiEndpoint;

    public function __construct()
    {
        $this->apiKey = config('services.sepay.api_key', env('SEPAY_API_KEY', ''));
        $this->accountNumber = config('services.vietqr.account_number', env('VIETQR_ACCOUNT_NUMBER', '0377466205'));
        $this->apiEndpoint = 'https://my.sepay.vn/userapi/transactions/list';
    }

    /**
     * Check real-time bank transactions via SePAY API for this order.
     * Returns true if payment was verified and updated to PAID.
     */
    public function verifyBankTransaction(Order $order): bool
    {
        if ($order->payment_status === 'PAID') {
            return true;
        }

        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(5)->get($this->apiEndpoint, [
                'account_number' => $this->accountNumber,
                'limit' => 20,
            ]);

            if (!$response->successful()) {
                Log::warning('⚠️ [SEPAY API] Tra cứu lịch sử giao dịch thất bại:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            $transactions = $data['transactions'] ?? $data['data'] ?? [];
            $orderCodeClean = preg_replace('/[^A-Z0-9]/', '', strtoupper($order->order_code));
            $expectedAmount = (int) $order->total_amount;

            foreach ($transactions as $tx) {
                $rawContent = strtoupper($tx['transaction_content'] ?? $tx['content'] ?? $tx['description'] ?? '');
                $contentClean = preg_replace('/[^A-Z0-9]/', '', $rawContent);
                $amountIn = (int) ($tx['amount_in'] ?? $tx['transferAmount'] ?? $tx['amount'] ?? 0);
                $referenceCode = $tx['reference_number'] ?? $tx['referenceCode'] ?? ('SEPAY_' . ($tx['id'] ?? time()));

                // Kiểm tra xem nội dung giao dịch ngân hàng có chứa mã đơn hàng và số tiền đủ không
                if ((str_contains($rawContent, strtoupper($order->order_code)) || (!empty($orderCodeClean) && str_contains($contentClean, $orderCodeClean))) 
                    && $amountIn >= $expectedAmount) {
                    Log::info("✅ [SEPAY API SUCCESS] Tìm thấy giao dịch thật từ ngân hàng cho đơn #{$order->order_code}:", $tx);

                    DB::transaction(function () use ($order, $referenceCode, $tx, $amountIn) {
                        $payment = Payment::firstOrCreate(
                            ['order_id' => $order->id],
                            [
                                'method' => 'BANK_TRANSFER',
                                'amount' => $amountIn,
                                'status' => 'PENDING',
                                'transaction_ref' => $referenceCode,
                            ]
                        );

                        $payment->update([
                            'status' => 'PAID',
                            'paid_at' => now(),
                            'amount' => $amountIn,
                            'transaction_ref' => $referenceCode,
                            'gateway_response' => json_encode($tx),
                        ]);

                        $order->update([
                            'payment_status' => 'PAID',
                        ]);
                    });

                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ [SEPAY API ERROR] Lỗi khi kết nối tới SePAY: ' . $e->getMessage());
        }

        return false;
    }
}
