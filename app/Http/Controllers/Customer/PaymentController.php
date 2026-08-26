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

        return view('customer.payment.qr', compact('order', 'paymentConfig', 'vietQrUrl', 'momoQrUrl', 'vnpayQrUrl', 'transferContent', 'amount'));
    }

    /**
     * Customer confirms they have completed the transfer.
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

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Thanh toán thành công! Đơn hàng của bạn đã được chuyển sang trạng thái ĐÃ XÁC NHẬN.');
    }
}
