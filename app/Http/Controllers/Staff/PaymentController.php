<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,PAID,FAILED,REFUNDED',
        ]);

        $payment->update([
            'status' => $validated['status'],
            'confirmed_by' => $validated['status'] === 'PAID' ? auth()->id() : $payment->confirmed_by,
            'paid_at' => $validated['status'] === 'PAID' ? now() : $payment->paid_at,
        ]);

        if ($validated['status'] === 'PAID') {
            $payment->order->update(['payment_status' => 'PAID']);
        } elseif ($validated['status'] === 'FAILED') {
            $payment->order->update(['payment_status' => 'FAILED']);
        } elseif ($validated['status'] === 'REFUNDED') {
            $payment->order->update(['payment_status' => 'REFUNDED']);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
    }
}
