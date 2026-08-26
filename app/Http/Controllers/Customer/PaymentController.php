<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show interactive QR Payment gateway for MoMo, VNPay or VietQR.
     */
    public function showQR(Order $order): View|\Illuminate\Http\RedirectResponse
    {
        $order->load(['details.product', 'payments']);

        // Bank / Merchant configurations for Mật Ngọt Bear
        $paymentConfig = [
            'bank_name' => 'MB Bank (Ngân hàng Quân Đội)',
            'bank_code' => 'MB',
            'account_number' => '0979896616',
            'account_name' => 'TIEM GAU BONG MAT NGOT BEAR',
            'momo_phone' => '0979896616',
            'momo_name' => 'TIEM GAU MAT NGOT BEAR',
        ];

        // Generate VietQR URL
        $transferContent = $order->order_code;
        $amount = (int) $order->total_amount;
        $vietQrUrl = "https://img.vietqr.io/image/MB-{$paymentConfig['account_number']}-compact2.png?amount={$amount}&addInfo=" . urlencode($transferContent) . "&accountName=" . urlencode($paymentConfig['account_name']);

        // Generate MoMo QR format URL
        $momoQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode("2|99|{$paymentConfig['momo_phone']}|{$paymentConfig['momo_name']}||0|0|{$amount}|{$transferContent}|transfer_myqr");

        // Generate VNPay Simulator QR
        $vnpayQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode("VNPAY-QR:{$order->order_code}:{$amount}:MAT_NGOT_BEAR");

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
