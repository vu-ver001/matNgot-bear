<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MomoService;
use App\Services\SepayService;
use App\Services\VietQrService;
use App\Services\VnpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MomoService $momoService,
        protected VnpayService $vnpayService,
        protected VietQrService $vietQrService,
        protected SepayService $sepayService
    ) {}

    /**
     * Show interactive QR Payment gateway for MoMo, VNPay or VietQR.
     */
    public function showQR(Order $order): View|RedirectResponse
    {
        $order->load(['details.product', 'payments']);

        $paymentConfig = array_merge(
            $this->vietQrService->getConfig(),
            $this->momoService->getConfig(),
            $this->vnpayService->getConfig()
        );

        $transferContent = $order->order_code;
        $amount = (int) $order->total_amount;

        $vietQrUrl = $this->vietQrService->generateQrUrl($order);
        $momoQrUrl = $this->momoService->generateQrUrl($order);
        $vnpayQrUrl = $this->vnpayService->generateQrUrl($order);

        $returnUrl = route('payment.vnpay.return');
        $vnpayGatewayUrl = $this->vnpayService->createPaymentUrl($order, $returnUrl, request()->ip() ?? '127.0.0.1');

        return view('customer.payment.qr', compact('order', 'paymentConfig', 'vietQrUrl', 'momoQrUrl', 'vnpayQrUrl', 'vnpayGatewayUrl', 'transferContent', 'amount'));
    }

    /**
     * Redirect customer directly to official VNPAY Payment Gateway.
     */
    public function redirectToVnpay(Order $order): RedirectResponse
    {
        $returnUrl = route('payment.vnpay.return');
        $paymentUrl = $this->vnpayService->createPaymentUrl($order, $returnUrl, request()->ip() ?? '127.0.0.1');

        Log::info("💳 [VNPAY REDIRECT] Khách hàng chuyển hướng sang cổng VNPay cho đơn hàng #{$order->order_code}", [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
        ]);

        return redirect()->away($paymentUrl);
    }

    /**
     * Redirect customer directly to official MoMo Payment Gateway.
     */
    public function redirectToMomo(Order $order): RedirectResponse
    {
        $returnUrl = route('payment.momo.return');
        $ipnUrl = route('payment.momo.ipn');
        $momoRes = $this->momoService->createGatewayPayment($order, $returnUrl, $ipnUrl);

        if (!empty($momoRes['success']) && !empty($momoRes['payUrl'])) {
            Log::info("👛 [MOMO REDIRECT] Khách hàng chuyển hướng sang cổng MoMo cho đơn hàng #{$order->order_code}", [
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'payUrl' => $momoRes['payUrl'],
            ]);
            return redirect()->away($momoRes['payUrl']);
        }

        return redirect()->route('customer.payment.qr', $order->id)
            ->with('info', $momoRes['message'] ?? 'Chuyển sang chế độ quét mã QR MoMo.');
    }

    /**
     * Handle return response callback from VNPAY Gateway (Browser redirect).
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        $inputData = $request->all();
        $isValidSignature = $this->vnpayService->verifyReturn($inputData);

        $orderCode = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $transactionNo = $request->input('vnp_TransactionNo', 'VNP' . time());
        $bankCode = $request->input('vnp_BankCode', 'VNPAY');
        $cardType = $request->input('vnp_CardType', 'CARD');
        $amountInVnp = (int) ($request->input('vnp_Amount') / 100);

        Log::info("📥 [VNPAY RETURN] Nhận phản hồi từ trình duyệt qua VNPay Return URL:", [
            'order_code' => $orderCode,
            'response_code' => $responseCode,
            'is_valid_signature' => $isValidSignature,
            'amount' => $amountInVnp,
        ]);

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng cần thanh toán.');
        }

        // Amount verification
        $isAmountValid = ((int) $order->total_amount === $amountInVnp);

        if ($isValidSignature && $responseCode === '00' && $isAmountValid) {
            // Update atomically if not already paid
            if ($order->payment_status !== 'PAID') {
                DB::transaction(function () use ($order, $transactionNo, $inputData) {
                    $payment = Payment::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'method' => 'CARD',
                            'amount' => $order->total_amount,
                            'status' => 'PENDING',
                            'transaction_ref' => $transactionNo,
                        ]
                    );

                    $payment->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                        'transaction_ref' => $transactionNo,
                        'gateway_response' => json_encode($inputData),
                    ]);

                    $order->update([
                        'payment_status' => 'PAID',
                    ]);
                });
            }

            return redirect()->route('payment.result', $order->id)
                ->with('success', "Thanh toán thành công qua cổng VNPAY ({$cardType} - {$bankCode})!");
        }

        $errorMessage = $this->vnpayService->getResponseMessage($responseCode ?? '99');

        return redirect()->route('payment.result', $order->id)
            ->with('error', "Thanh toán VNPay chưa thành công: {$errorMessage}");
    }

    /**
     * Handle Server-to-Server IPN from VNPAY Gateway (Backend verification).
     */
    public function vnpayIpn(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $isValidSignature = $this->vnpayService->verifyIpn($inputData);

        $orderCode = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $transactionNo = $request->input('vnp_TransactionNo', 'VNP' . time());
        $amountInVnp = (int) (($request->input('vnp_Amount') ?? 0) / 100);

        Log::info("🔔 [VNPAY IPN] Server VNPay gọi Webhook IPN:", [
            'order_code' => $orderCode,
            'response_code' => $responseCode,
            'is_valid_signature' => $isValidSignature,
            'amount' => $amountInVnp,
        ]);

        if (!$isValidSignature) {
            Log::warning("⚠️ [VNPAY IPN] Chữ ký VNPay không hợp lệ!", ['data' => $inputData]);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            Log::warning("⚠️ [VNPAY IPN] Không tìm thấy đơn hàng: {$orderCode}");
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if ((int) $order->total_amount !== $amountInVnp) {
            Log::warning("⚠️ [VNPAY IPN] Số tiền không khớp!", [
                'expected' => $order->total_amount,
                'received' => $amountInVnp,
            ]);
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        // Idempotency: Check if already paid
        if ($order->payment_status === 'PAID') {
            Log::info("ℹ️ [VNPAY IPN] Đơn hàng #{$order->order_code} đã được xác nhận thanh toán trước đó (Idempotent).");
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        if ($responseCode === '00') {
            DB::transaction(function () use ($order, $transactionNo, $inputData) {
                $payment = Payment::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'method' => 'CARD',
                        'amount' => $order->total_amount,
                        'status' => 'PENDING',
                        'transaction_ref' => $transactionNo,
                    ]
                );

                $payment->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                    'transaction_ref' => $transactionNo,
                    'gateway_response' => json_encode($inputData),
                ]);

                $order->update([
                    'payment_status' => 'PAID',
                ]);
            });

            Log::info("✅ [VNPAY IPN SUCCESS] Đơn hàng #{$order->order_code} đã được cập nhật PAID thành công qua IPN.");
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        }

        // Failed payment
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            ['method' => 'CARD', 'amount' => $order->total_amount, 'status' => 'FAILED']
        );
        $payment->update(['status' => 'FAILED', 'gateway_response' => json_encode($inputData)]);

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    /**
     * Handle return response callback from MoMo Gateway (Browser redirect).
     */
    public function momoReturn(Request $request): RedirectResponse
    {
        $data = $request->all();
        $isValidSignature = $this->momoService->verifyReturnSignature($data);
        $orderId = $this->momoService->extractOrderId($data);
        $resultCode = (int) ($data['resultCode'] ?? -1);
        $transId = $data['transId'] ?? ('MOMO' . time());
        $amount = (int) ($data['amount'] ?? 0);

        Log::info("📥 [MOMO RETURN] Nhận phản hồi từ trình duyệt qua MoMo Return URL:", [
            'order_id' => $orderId,
            'result_code' => $resultCode,
            'is_valid_signature' => $isValidSignature,
            'amount' => $amount,
        ]);

        $order = $orderId ? Order::find($orderId) : null;

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng cần thanh toán.');
        }

        $isAmountValid = ((int) $order->total_amount === $amount);

        if ($isValidSignature && $resultCode === 0 && $isAmountValid) {
            if ($order->payment_status !== 'PAID') {
                DB::transaction(function () use ($order, $transId, $data) {
                    $payment = Payment::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'method' => 'E_WALLET',
                            'amount' => $order->total_amount,
                            'status' => 'PENDING',
                            'transaction_ref' => $transId,
                        ]
                    );

                    $payment->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                        'transaction_ref' => $transId,
                        'gateway_response' => json_encode($data),
                    ]);

                    $order->update([
                        'payment_status' => 'PAID',
                    ]);
                });
            }

            return redirect()->route('payment.result', $order->id)
                ->with('success', 'Thanh toán thành công qua Ví MoMo!');
        }

        $errorMessage = $data['message'] ?? 'Giao dịch MoMo chưa hoàn tất hoặc bị hủy.';

        return redirect()->route('payment.result', $order->id)
            ->with('error', "Thanh toán MoMo chưa thành công: {$errorMessage}");
    }

    /**
     * Handle Server-to-Server IPN from MoMo Gateway (Backend verification).
     */
    public function momoIpn(Request $request): JsonResponse
    {
        $data = $request->all();
        $isValidSignature = $this->momoService->verifyIpnSignature($data);
        $orderId = $this->momoService->extractOrderId($data);
        $resultCode = (int) ($data['resultCode'] ?? -1);
        $transId = $data['transId'] ?? ('MOMO' . time());
        $amount = (int) ($data['amount'] ?? 0);

        Log::info("🔔 [MOMO IPN] Server MoMo gọi Webhook IPN:", [
            'order_id' => $orderId,
            'result_code' => $resultCode,
            'is_valid_signature' => $isValidSignature,
            'amount' => $amount,
        ]);

        if (!$isValidSignature) {
            Log::warning("⚠️ [MOMO IPN] Chữ ký MoMo không hợp lệ!", ['data' => $data]);
            return response()->json(['message' => 'Invalid signature', 'resultCode' => 97], 400);
        }

        $order = $orderId ? Order::find($orderId) : null;
        if (!$order) {
            Log::warning("⚠️ [MOMO IPN] Không tìm thấy đơn hàng ID: {$orderId}");
            return response()->json(['message' => 'Order not found', 'resultCode' => 1], 404);
        }

        if ((int) $order->total_amount !== $amount) {
            Log::warning("⚠️ [MOMO IPN] Số tiền không khớp!", [
                'expected' => $order->total_amount,
                'received' => $amount,
            ]);
            return response()->json(['message' => 'Amount mismatch', 'resultCode' => 4], 400);
        }

        // Idempotency: Check if already paid
        if ($order->payment_status === 'PAID') {
            Log::info("ℹ️ [MOMO IPN] Đơn hàng #{$order->order_code} đã được xác nhận thanh toán trước đó (Idempotent).");
            return response()->json(['message' => 'Order already confirmed', 'resultCode' => 0]);
        }

        if ($resultCode === 0) {
            DB::transaction(function () use ($order, $transId, $data) {
                $payment = Payment::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'method' => 'E_WALLET',
                        'amount' => $order->total_amount,
                        'status' => 'PENDING',
                        'transaction_ref' => $transId,
                    ]
                );

                $payment->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                    'transaction_ref' => $transId,
                    'gateway_response' => json_encode($data),
                ]);

                $order->update([
                    'payment_status' => 'PAID',
                ]);
            });

            Log::info("✅ [MOMO IPN SUCCESS] Đơn hàng #{$order->order_code} đã được cập nhật PAID thành công qua IPN.");
            return response()->json(['message' => 'Success', 'resultCode' => 0]);
        }

        // Failed payment
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            ['method' => 'E_WALLET', 'amount' => $order->total_amount, 'status' => 'FAILED']
        );
        $payment->update(['status' => 'FAILED', 'gateway_response' => json_encode($data)]);

        return response()->json(['message' => 'Payment failed', 'resultCode' => 0]);
    }

    /**
     * Unified Payment Result Page (Displays real-time status from DB).
     */
    public function paymentResult(Order $order): View
    {
        $order->load(['details.product', 'payments']);
        $latestPayment = $order->payments()->latest()->first();

        return view('customer.payment.result', compact('order', 'latestPayment'));
    }

    /**
     * Retry Payment / Change Payment Method for a pending or failed order.
     */
    public function retryPayment(Order $order, Request $request): RedirectResponse
    {
        $rawMethod = $request->input('payment_method', $order->payment_method);

        // Normalize method name
        $method = match ($rawMethod) {
            'VNPAY', 'CARD' => 'CARD',
            'MOMO', 'E_WALLET' => 'E_WALLET',
            'COD' => 'COD',
            default => 'BANK_TRANSFER',
        };

        $order->update(['payment_method' => $method]);

        if ($method === 'COD') {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Đã chuyển phương thức thanh toán sang: Thanh toán khi nhận hàng (COD)!');
        }

        // Record a new payment attempt
        Payment::create([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $order->total_amount,
            'status' => 'PENDING',
            'transaction_ref' => 'RETRY_' . time() . '_' . $order->id,
        ]);

        if ($method === 'CARD') {
            return $this->redirectToVnpay($order);
        }

        if ($method === 'E_WALLET') {
            return $this->redirectToMomo($order);
        }

        return redirect()->route('customer.payment.qr', $order->id);
    }

    /**
     * Check real-time payment status of an order for frontend auto-polling.
     * Checks database status and real-time bank transaction match via SePAY.
     */
    public function checkStatus(Order $order): JsonResponse
    {
        // 1. Kiểm tra trạng thái trong Database
        if ($order->payment_status === 'PAID') {
            return response()->json([
                'success' => true,
                'paid' => true,
                'payment_status' => 'PAID',
                'order_status' => $order->order_status,
                'redirect_url' => route('payment.result', $order->id),
            ]);
        }

        // 2. Tra cứu đối soát giao dịch thực tế từ tài khoản ngân hàng (SePAY / MB Bank)
        $isBankVerified = $this->sepayService->verifyBankTransaction($order);
        if ($isBankVerified) {
            $order->refresh();
            return response()->json([
                'success' => true,
                'paid' => true,
                'payment_status' => 'PAID',
                'order_status' => $order->order_status,
                'redirect_url' => route('payment.result', $order->id),
            ]);
        }

        return response()->json([
            'success' => true,
            'paid' => false,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'redirect_url' => null,
        ]);
    }

    /**
     * Simulate incoming bank payment transaction (Instant confirmation for demo / presentation).
     */
    public function simulatePayment(Request $request, Order $order): JsonResponse
    {
        $transactionNo = 'SIM' . strtoupper(uniqid());

        DB::transaction(function () use ($order, $transactionNo) {
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => $order->payment_method,
                    'amount' => $order->total_amount,
                    'status' => 'PENDING',
                    'transaction_ref' => $transactionNo,
                ]
            );

            $payment->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'transaction_ref' => $transactionNo,
                'gateway_response' => json_encode([
                    'type' => 'SIMULATED_WEBHOOK',
                    'order_code' => $order->order_code,
                    'amount' => $order->total_amount,
                    'simulated_at' => now()->toIso8601String(),
                ]),
            ]);

            $order->update([
                'payment_status' => 'PAID',
                'order_status' => $order->order_status === 'PENDING' ? 'CONFIRMED' : $order->order_status,
                'confirmed_at' => now(),
            ]);
        });

        Log::info("⚡ [PAYMENT SIMULATION] Đơn hàng #{$order->order_code} đã được tự động xác nhận thanh toán thành công.");

        return response()->json([
            'success' => true,
            'message' => "Tự động nhận diện thanh toán thành công cho đơn hàng #{$order->order_code}!",
            'redirect_url' => route('payment.result', $order->id),
        ]);
    }

    /**
     * Webhook listener for SePAY / Casso / Bank IPN (Tự động nhận diện biến động số dư 100%).
     * Follows strict 6-step backend flow:
     * 10. Kiểm tra chữ ký / Token bảo mật
     * 11. Kiểm tra mã đơn hàng
     * 12. Kiểm tra số tiền
     * 13. Kiểm tra giao dịch đã xử lý chưa (Idempotency)
     * 14. Cập nhật payment_status = "PAID"
     * 15. Trả kết quả JSON cho bên gửi & hệ thống frontend
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('📥 [PAYMENT WEBHOOK] Nhận tín hiệu biến động số dư từ Cổng thanh toán / Ngân hàng:', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        // ==============================================================
        // BƯỚC 10: KIỂM TRA CHỮ KÝ / API KEY / WEBHOOK SECRET (NẾU CÓ CẤU HÌNH)
        // ==============================================================
        $expectedApiKey = config('services.sepay.api_key', env('SEPAY_API_KEY', env('PAYMENT_WEBHOOK_SECRET', '')));
        $expectedSecret = env('SEPAY_WEBHOOK_SECRET', '');
        
        if (!empty($expectedApiKey) || !empty($expectedSecret)) {
            $authHeader = $request->header('Authorization', '');
            $customApiKey = $request->header('x-api-key', $request->header('apikey', $request->input('apiKey', '')));
            $sepaySignature = $request->header('x-sepay-signature', $request->header('signature', ''));

            $isValidAuth = false;

            // 1. Kiểm tra API Key / Token trong header hoặc query
            if (!empty($expectedApiKey)) {
                if (str_contains($authHeader, $expectedApiKey) || $customApiKey === $expectedApiKey) {
                    $isValidAuth = true;
                }
            }

            // 2. Kiểm tra chữ ký HMAC-SHA256 nếu có secret
            if (!$isValidAuth && !empty($expectedSecret) && !empty($sepaySignature)) {
                $calculatedSignature = hash_hmac('sha256', $request->getContent(), $expectedSecret);
                if (hash_equals($calculatedSignature, $sepaySignature)) {
                    $isValidAuth = true;
                }
            }

            // Nếu người dùng chọn "Không xác thực" trên SePAY thì vẫn cho phép qua nếu không gửi header
            if (!$isValidAuth && empty($authHeader) && empty($customApiKey) && empty($sepaySignature)) {
                $isValidAuth = true;
            }

            if (!$isValidAuth) {
                Log::warning('⚠️ [PAYMENT WEBHOOK] Chữ ký hoặc API Key Webhook không khớp!', [
                    'received_auth' => $authHeader,
                    'received_key' => $customApiKey,
                    'received_sig' => $sepaySignature,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Chữ ký hoặc API Key không hợp lệ.',
                ], 401);
            }
        }

        // ==============================================================
        // BƯỚC 11: KIỂM TRA MÃ ĐƠN HÀNG
        // ==============================================================
        $content = $request->input('content') 
            ?? $request->input('description') 
            ?? $request->input('order_code') 
            ?? $request->input('orderCode') 
            ?? $request->input('code') 
            ?? '';

        $transferAmount = (float) (
            $request->input('transferAmount') 
            ?? $request->input('amount') 
            ?? $request->input('transfer_amount') 
            ?? 0
        );

        $transactionRef = (string) (
            $request->input('referenceCode') 
            ?? $request->input('transaction_id') 
            ?? $request->input('id') 
            ?? ('WH' . time() . '_' . rand(100, 999))
        );

        $order = null;
        if (!empty($content)) {
            // Tìm kiếm các mẫu mã đơn hàng phổ biến: MNB..., BEAR-..., hoặc chính xác nội dung
            if (preg_match('/(MNB[A-Z0-9]+)/i', $content, $matches)) {
                $order = Order::where('order_code', strtoupper($matches[1]))->first();
            } elseif (preg_match('/(BEAR-[A-Z0-9]+)/i', $content, $matches)) {
                $order = Order::where('order_code', strtoupper($matches[1]))->first();
            } elseif (preg_match('/#?([A-Z0-9]{8,25})/i', $content, $matches)) {
                $order = Order::where('order_code', strtoupper($matches[1]))->first();
            } else {
                $order = Order::where('order_code', trim($content))->first();
            }
        }

        if (!$order) {
            Log::warning('⚠️ [PAYMENT WEBHOOK] Không tìm thấy đơn hàng tương ứng với nội dung chuyển khoản:', [
                'content' => $content,
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng tương ứng với mã trong nội dung chuyển khoản.',
                'received_content' => $content,
            ], 404);
        }

        // ==============================================================
        // BƯỚC 12: KIỂM TRA SỐ TIỀN
        // ==============================================================
        $expectedAmount = (int) $order->total_amount;
        if ($transferAmount > 0 && (int)$transferAmount < $expectedAmount) {
            Log::warning("⚠️ [PAYMENT WEBHOOK] Số tiền thanh toán không đủ cho đơn hàng #{$order->order_code}:", [
                'expected' => $expectedAmount,
                'received' => $transferAmount,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Số tiền chuyển khoản ({$transferAmount}đ) không đủ so với giá trị đơn hàng ({$expectedAmount}đ).",
                'expected_amount' => $expectedAmount,
                'received_amount' => $transferAmount,
            ], 400);
        }

        // ==============================================================
        // BƯỚC 13: KIỂM TRA GIAO DỊCH ĐÃ XỬ LÝ CHƯA (IDEMPOTENCY)
        // ==============================================================
        if ($order->payment_status === 'PAID') {
            Log::info("ℹ️ [PAYMENT WEBHOOK] Đơn hàng #{$order->order_code} đã được xử lý thanh toán trước đó (Idempotent).");
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được xác nhận thanh toán trước đó.',
                'order_code' => $order->order_code,
                'payment_status' => 'PAID',
            ]);
        }

        // ==============================================================
        // BƯỚC 14: CẬP NHẬT PAYMENT_STATUS = "PAID"
        // ==============================================================
        DB::transaction(function () use ($order, $transactionRef, $payload, $transferAmount) {
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => $order->payment_method ?? 'BANK_TRANSFER',
                    'amount' => $transferAmount > 0 ? $transferAmount : $order->total_amount,
                    'status' => 'PENDING',
                    'transaction_ref' => $transactionRef,
                ]
            );

            $payment->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'amount' => $transferAmount > 0 ? $transferAmount : $order->total_amount,
                'transaction_ref' => $transactionRef,
                'gateway_response' => json_encode($payload),
            ]);

            $order->update([
                'payment_status' => 'PAID',
            ]);
        });

        Log::info("✅ [PAYMENT WEBHOOK SUCCESS] Đơn hàng #{$order->order_code} đã TỰ ĐỘNG XÁC NHẬN THANH TOÁN thành công qua Webhook!");

        // ==============================================================
        // BƯỚC 15: TRẢ KẾT QUẢ CHO HỆ THỐNG / FRONTEND
        // ==============================================================
        return response()->json([
            'success' => true,
            'message' => "Đã tự động xác nhận thanh toán đơn hàng #{$order->order_code} thành công.",
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'payment_status' => 'PAID',
            'redirect_url' => route('payment.result', $order->id),
        ]);
    }

    /**
     * Customer confirms they have completed the transfer manually.
     */
    public function confirmPayment(Request $request, Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => $order->payment_method,
                    'amount' => $order->total_amount,
                    'status' => 'PAID',
                    'transaction_ref' => 'TXN' . strtoupper(uniqid()),
                    'paid_at' => now(),
                ]
            );

            $payment->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'transaction_ref' => $payment->transaction_ref ?? ('TXN' . strtoupper(uniqid())),
            ]);

            $order->update([
                'payment_status' => 'PAID',
            ]);
        });

        return redirect()->route('payment.result', $order->id)
            ->with('success', 'Thanh toán thành công! Đơn hàng đang chờ nhân viên xác nhận.');
    }
}
