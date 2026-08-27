<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MomoService;
use App\Services\VietQrService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MomoService $momoService,
        protected VnpayService $vnpayService,
        protected VietQrService $vietQrService
    ) {}

    /**
     * Show interactive QR Payment gateway for MoMo, VNPay or VietQR.
     */
    public function showQR(Order $order): View|\Illuminate\Http\RedirectResponse
    {
        $order->load(['details.product', 'payments']);

        // Merge configs from all services
        $paymentConfig = array_merge(
            $this->vietQrService->getConfig(),
            $this->momoService->getConfig(),
            $this->vnpayService->getConfig()
        );

        $transferContent = $order->order_code;
        $amount = (int) $order->total_amount;

        // Generate QRs via dedicated services
        $vietQrUrl = $this->vietQrService->generateQrUrl($order);
        $momoQrUrl = $this->momoService->generateQrUrl($order);
        $vnpayQrUrl = $this->vnpayService->generateQrUrl($order);

        // Generate VNPAY Gateway Link for Card/ATM/Visa payment
        $returnUrl = route('customer.payment.vnpay.return');
        $vnpayGatewayUrl = $this->vnpayService->createPaymentUrl($order, $returnUrl, request()->ip() ?? '127.0.0.1');

        return view('customer.payment.qr', compact('order', 'paymentConfig', 'vietQrUrl', 'momoQrUrl', 'vnpayQrUrl', 'vnpayGatewayUrl', 'transferContent', 'amount'));
    }

    /**
     * Redirect customer directly to official VNPAY Payment Gateway (Visa/Mastercard/ATM/QR).
     */
    public function redirectToVnpay(Order $order)
    {
        $returnUrl = route('customer.payment.vnpay.return');
        $paymentUrl = $this->vnpayService->createPaymentUrl($order, $returnUrl, request()->ip() ?? '127.0.0.1');

        return redirect()->away($paymentUrl);
    }

    /**
     * Handle return response callback from VNPAY Gateway.
     */
    public function vnpayReturn(Request $request)
    {
        $inputData = $request->all();
        $isValidSignature = $this->vnpayService->verifyResponse($inputData);

        $orderCode = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $transactionNo = $request->input('vnp_TransactionNo', 'VNP' . time());
        $bankCode = $request->input('vnp_BankCode', 'VNPAY');
        $cardType = $request->input('vnp_CardType', 'CARD');

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng cần thanh toán.');
        }

        if ($isValidSignature && $responseCode === '00') {
            // Payment success: Update Order & Payment
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => 'CARD',
                    'amount' => $order->total_amount,
                    'status' => 'PAID',
                    'transaction_ref' => $transactionNo,
                    'paid_at' => now(),
                ]
            );

            $payment->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'transaction_ref' => $transactionNo,
            ]);

            $order->update([
                'payment_status' => 'PAID',
                'order_status' => $order->order_status === 'PENDING' ? 'CONFIRMED' : $order->order_status,
                'confirmed_at' => now(),
            ]);

            return redirect()->route('customer.checkout.success', $order->id)
                ->with('success', "Thanh toán thành công qua cổng VNPAY ({$cardType} - {$bankCode})! Đơn hàng đã được xác nhận.");
        }

        $errorMessage = $this->vnpayService->getResponseMessage($responseCode ?? '99');

        return redirect()->route('customer.payment.qr', $order->id)
            ->with('error', "Thanh toán VNPAY chưa hoàn tất: {$errorMessage}. Bạn có thể thử lại hoặc chọn phương thức khác.");
    }

    /**
     * Customer confirms they have completed the transfer manually.
     */
    public function confirmPayment(Request $request, Order $order)
    {
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
            'order_status' => $order->order_status === 'PENDING' ? 'CONFIRMED' : $order->order_status,
            'confirmed_at' => now(),
        ]);

        return redirect()->route('customer.checkout.success', $order->id)
            ->with('success', 'Thanh toán thành công! Đơn hàng của bạn đã được chuyển sang trạng thái ĐÃ XÁC NHẬN.');
    }
}
