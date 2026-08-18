<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,PAID,FAILED,REFUNDED',
        ]);

        try {
            match ($validated['status']) {
                'PAID' => $this->orderService->confirmPayment($payment, auth()->id()),
                'FAILED' => $this->orderService->markPaymentFailed($payment),
                'REFUNDED' => $this->orderService->refundPayment($payment),
                default => null,
            };
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }
}
